<?php
require_once 'config.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Debug mode - add ?debug=1 to URL to see diagnostics
$debugMode = isset($_GET['debug']);

// Handle session deletion
if (isset($_GET['delete_session'])) {
    $sessionId = $_GET['delete_session'];
    
    // Check if session can be deleted (not active/pending)
    $checkQuery = "SELECT status FROM voting_sessions WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $sessionStatus = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($sessionStatus && in_array($sessionStatus['status'], ['active', 'pending', 'paused'])) {
        $message = ' Cannot delete an active, pending, or paused session! Lock the session first.';
        $messageType = 'error';
    } else {
        // Delete all votes for this session
        $stmt = $conn->prepare("DELETE FROM votes WHERE session_id = ?");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $deletedVotes = $stmt->affected_rows;
        $stmt->close();
        
        // Delete the session
        $stmt = $conn->prepare("DELETE FROM voting_sessions WHERE id = ?");
        $stmt->bind_param("i", $sessionId);
        if ($stmt->execute()) {
            $message = ' Session deleted successfully! Removed ' . $deletedVotes . ' vote records.';
            $messageType = 'success';
        } else {
            $message = ' Failed to delete session.';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Get all voting sessions with vote counts - FIXED QUERY
$sessionsQuery = "SELECT vs.id, vs.session_name, vs.status, vs.created_at,
                  (SELECT COUNT(*) FROM votes v WHERE v.session_id = vs.id) as total_votes,
                  (SELECT COUNT(DISTINCT v.voter_id) FROM votes v WHERE v.session_id = vs.id) as unique_voters
                  FROM voting_sessions vs 
                  ORDER BY vs.id DESC";

if ($debugMode) {
    echo "<!-- DEBUG: Executing sessions query -->\n";
}

$sessions = $conn->query($sessionsQuery);

if (!$sessions) {
    die("Database query failed: " . $conn->error);
}

if ($debugMode) {
    echo "<!-- DEBUG: Found " . $sessions->num_rows . " sessions -->\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Election History</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
        }
        
        .navbar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 1.5em;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
        }
        
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .message.success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .message.error {
            background: #fed7d7;
            color: #c53030;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #10b981;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .info-banner {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-banner strong {
            color: #1e40af;
        }
        
        .debug-banner {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9em;
        }
        
        .session-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
            position: relative;
            transition: all 0.3s;
        }
        
        .session-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .session-card.active-session {
            border-left-color: #34d399;
            background: #ecfdf5;
        }
        
        .session-card.locked-session {
            border-left-color: #f56565;
        }
        
        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .session-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2d3748;
        }
        
        .header-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-badge {
            padding: 6px 16px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-locked {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-pending {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .badge-paused {
            background: #feebc8;
            color: #744210;
        }
        
        .session-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .vote-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #10b981;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .vote-details {
            margin-top: 15px;
        }
        
        .vote-details summary {
            cursor: pointer;
            font-weight: 600;
            color: #10b981;
            padding: 10px;
            background: white;
            border-radius: 5px;
            user-select: none;
        }
        
        .vote-details summary:hover {
            background: #f7fafc;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: white;
            color: #10b981;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .results-table tr:last-child td {
            border-bottom: none;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9em;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #10b981;
            color: white;
        }
        
        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
        }
        
        .btn-disabled {
            background: #cbd5e0;
            color: #a0aec0;
            cursor: not-allowed;
        }
        
        .btn-disabled:hover {
            transform: none;
        }
        
        .warning-banner {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .warning-banner strong {
            color: #92400e;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #718096;
        }
        
        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .session-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .vote-stats {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Audit Logs - Election History</h1>
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2> Complete Voting Session History</h2>
            
            <div class="info-banner">
                <strong> About Audit Logs:</strong> This page shows all voting sessions ever conducted, including vote counts and position breakdowns. Sessions can only be deleted if they are locked (completed).
            </div>
            
            <?php if ($debugMode): ?>
            <div class="debug-banner">
                <strong>🔧 DEBUG MODE ACTIVE</strong><br>
                Total sessions found: <?php echo $sessions->num_rows; ?><br>
                <?php
                // Test direct vote count
                $testVoteQuery = "SELECT COUNT(*) as total FROM votes";
                $testResult = $conn->query($testVoteQuery);
                $testCount = $testResult->fetch_assoc()['total'];
                echo "Total votes in database: " . $testCount . "<br>";
                
                // Test votes per session
                $testSessionVotes = "SELECT session_id, COUNT(*) as count FROM votes GROUP BY session_id";
                $testSessionResult = $conn->query($testSessionVotes);
                echo "Votes per session:<br>";
                while ($row = $testSessionResult->fetch_assoc()) {
                    echo "  Session " . $row['session_id'] . ": " . $row['count'] . " votes<br>";
                }
                ?>
            </div>
            <?php endif; ?>
            
            <div class="warning-banner">
                <strong> Deletion Warning:</strong> Deleting a session will permanently remove all voting records and cannot be undone. Active, pending, or paused sessions cannot be deleted.
            </div>
            
            <?php if ($sessions->num_rows > 0): ?>
                <?php while ($session = $sessions->fetch_assoc()): 
                    $sessionId = $session['id'];
                    $totalVotes = $session['total_votes'];
                    $uniqueVoters = $session['unique_voters'];
                    
                    if ($debugMode) {
                        echo "<!-- DEBUG: Session $sessionId has $totalVotes votes from $uniqueVoters voters -->\n";
                    }
                    
                    // Get vote breakdown by position for this session
                    // UPDATED: Handle votes even if candidates are deleted
                    $breakdownQuery = "SELECT p.position_name, p.position_order, COUNT(v.id) as vote_count
                                     FROM positions p
                                     LEFT JOIN votes v ON p.id = v.position_id AND v.session_id = ?
                                     GROUP BY p.id, p.position_name, p.position_order
                                     ORDER BY p.position_order";
                    $stmt = $conn->prepare($breakdownQuery);
                    $stmt->bind_param("i", $sessionId);
                    $stmt->execute();
                    $breakdown = $stmt->get_result();
                    
                    // Get winner for each position
                    $winnersQuery = "SELECT p.position_name, u.full_name as winner_name, w.vote_count
                                    FROM winners w
                                    JOIN positions p ON w.position_id = p.id
                                    LEFT JOIN users u ON w.user_id = u.id
                                    WHERE w.session_id = ?
                                    ORDER BY p.position_order";
                    $winnersStmt = $conn->prepare($winnersQuery);
                    $winnersStmt->bind_param("i", $sessionId);
                    $winnersStmt->execute();
                    $winners = $winnersStmt->get_result();
                    
                    $isActiveSession = in_array($session['status'], ['active', 'pending', 'paused']);
                ?>
                
                <div class="session-card <?php echo $isActiveSession ? 'active-session' : ($session['status'] == 'locked' ? 'locked-session' : ''); ?>">
                    <div class="session-header">
                        <div class="session-name">
                            <?php echo htmlspecialchars($session['session_name']); ?>
                        </div>
                        <div class="header-controls">
                            <span class="status-badge badge-<?php echo $session['status']; ?>">
                                <?php echo strtoupper($session['status']); ?>
                            </span>
                            <a href="view_results.php?session_id=<?php echo $sessionId; ?>" 
                               class="btn btn-primary">
                                 View Results
                            </a>
                            <?php if (!$isActiveSession): ?>
                                <a href="?delete_session=<?php echo $sessionId; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm(' Delete Session: <?php echo htmlspecialchars($session['session_name']); ?>?\n\nThis will permanently remove:\n- All vote records (<?php echo $totalVotes; ?> votes)\n- Session data\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?')">
                                     Delete
                                </a>
                            <?php else: ?>
                                <button class="btn btn-disabled" disabled title="Cannot delete active session">
                                     Cannot Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="session-meta">
                        <strong> Created:</strong> <?php echo date('F d, Y h:i A', strtotime($session['created_at'])); ?>
                        <?php if ($isActiveSession): ?>
                            <span style="color: #10b981; font-weight: 600; margin-left: 15px;">🔴 Currently Running</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="vote-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $totalVotes; ?></div>
                            <div class="stat-label">Total Votes Cast</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $uniqueVoters; ?></div>
                            <div class="stat-label">Students Voted</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $breakdown->num_rows; ?></div>
                            <div class="stat-label">Positions</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $winners->num_rows; ?></div>
                            <div class="stat-label">Winners Elected</div>
                        </div>
                    </div>
                    
                    <details class="vote-details" style="margin-top: 20px;">
                        <summary> View Detailed Breakdown</summary>
                        
                        <?php if ($winners->num_rows > 0): ?>
                            <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 10px;">
                                <h4 style="color: #10b981; margin-bottom: 10px;">🏆 Election Winners:</h4>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Position</th>
                                            <th>Winner</th>
                                            <th>Votes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $winners->data_seek(0);
                                        while ($winner = $winners->fetch_assoc()): 
                                        ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($winner['position_name']); ?></strong></td>
                                                <td>
                                                    <?php if ($winner['winner_name']): ?>
                                                        <?php echo htmlspecialchars($winner['winner_name']); ?>
                                                    <?php else: ?>
                                                        <span style="color: #a0aec0;">No winner yet</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $winner['vote_count']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        
                        <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 10px;">
                            <h4 style="color: #10b981; margin-bottom: 10px;">📋 Vote Breakdown by Position:</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Position</th>
                                        <th>Votes Cast</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $breakdown->data_seek(0);
                                    while ($row = $breakdown->fetch_assoc()): 
                                    ?>
                                        <tr>
                                            <td><strong>#<?php echo $row['position_order']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['position_name']); ?></td>
                                            <td>
                                                <strong><?php echo $row['vote_count']; ?></strong> votes
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
                
                <?php 
                    $stmt->close();
                    $winnersStmt->close();
                endwhile; 
                ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3 style="color: #10b981; margin-bottom: 15px;">No Voting Sessions Yet</h3>
                    <p>There are no voting sessions recorded in the system.</p>
                    <p style="margin-top: 10px;">Create your first session to get started!</p>
                    <a href="create_session.php" class="btn btn-primary" style="margin-top: 20px; padding: 12px 30px;">
                         Create New Session
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2> Tips for Managing Election History</h2>
            <ul style="color: #4a5568; line-height: 2; margin-left: 20px;">
                <li><strong>View Results:</strong> Click "View Results" to see detailed election outcomes for any past session</li>
                <li><strong>Keep Records:</strong> All sessions are automatically saved for audit purposes</li>
                <li><strong>Export Data:</strong> Use the export buttons in the results view to save reports</li>
                <li><strong>Delete Old Sessions:</strong> Only locked sessions can be deleted to prevent accidental data loss</li>
                <li><strong>Debug Mode:</strong> Add <code>?debug=1</code> to the URL to see diagnostic information</li>
                <li><strong>Session Status:</strong>
                    <ul style="margin-top: 10px;">
                        <li><span class="status-badge badge-active" style="margin: 5px;">Active</span> - Currently accepting votes</li>
                        <li><span class="status-badge badge-pending" style="margin: 5px;">Pending</span> - Created but not started</li>
                        <li><span class="status-badge badge-paused" style="margin: 5px;">Paused</span> - Temporarily stopped</li>
                        <li><span class="status-badge badge-locked" style="margin: 5px;">Locked</span> - Completed and finalized</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>