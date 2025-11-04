<?php
require_once 'config.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle candidate nomination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nominate'])) {
    $userId = $_POST['user_id'];
    $positionId = $_POST['position_id'];
    
    // Get the position order for the position they're trying to nominate for
    $posOrderQuery = "SELECT position_order, position_name FROM positions WHERE id = ?";
    $stmt = $conn->prepare($posOrderQuery);
    $stmt->bind_param("i", $positionId);
    $stmt->execute();
    $targetPosition = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Check if student has already won a higher-priority position
    $electedCheckQuery = "SELECT p.position_order, p.position_name 
                          FROM candidates c
                          JOIN positions p ON c.position_id = p.id
                          WHERE c.user_id = ? AND c.status = 'elected'
                          ORDER BY p.position_order ASC
                          LIMIT 1";
    $stmt = $conn->prepare($electedCheckQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $electedResult = $stmt->get_result();
    
    if ($electedResult->num_rows > 0) {
        $electedPosition = $electedResult->fetch_assoc();
        
        // If they won a higher priority position (lower order number), prevent nomination
        if ($electedPosition['position_order'] < $targetPosition['position_order']) {
            $studentQuery = "SELECT full_name FROM users WHERE id = ?";
            $stmtStudent = $conn->prepare($studentQuery);
            $stmtStudent->bind_param("i", $userId);
            $stmtStudent->execute();
            $studentName = $stmtStudent->get_result()->fetch_assoc()['full_name'];
            $stmtStudent->close();
            
            $message = ' Cannot nominate ' . htmlspecialchars($studentName) . ' for ' . 
                       htmlspecialchars($targetPosition['position_name']) . '! They have already been elected as ' . 
                       htmlspecialchars($electedPosition['position_name']) . ' (higher priority position).';
            $messageType = 'error';
            $stmt->close();
        } else {
            $stmt->close();
            // They can be nominated for same or higher priority positions
            nominateCandidate($conn, $userId, $positionId, $message, $messageType);
        }
    } else {
        $stmt->close();
        // No elected position found, proceed with normal checks
        nominateCandidate($conn, $userId, $positionId, $message, $messageType);
    }
}

// Function to handle candidate nomination
function nominateCandidate($conn, $userId, $positionId, &$message, &$messageType) {
    // Check if already nominated
    $checkQuery = "SELECT id FROM candidates WHERE user_id = ? AND position_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $userId, $positionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = 'Student is already nominated for this position!';
        $messageType = 'warning';
    } else {
        $insertStmt = $conn->prepare("INSERT INTO candidates (user_id, position_id) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $userId, $positionId);
        if ($insertStmt->execute()) {
            $message = ' Candidate nominated successfully!';
            $messageType = 'success';
        } else {
            $message = ' Failed to nominate candidate!';
            $messageType = 'error';
        }
        $insertStmt->close();
    }
    $stmt->close();
}

// Handle candidate removal
if (isset($_GET['remove'])) {
    $candidateId = $_GET['remove'];
    $deleteStmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $deleteStmt->bind_param("i", $candidateId);
    if ($deleteStmt->execute()) {
        $message = 'Candidate removed successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to remove candidate!';
        $messageType = 'error';
    }
    $deleteStmt->close();
}

// Get all students
$studentsQuery = "SELECT id, student_id, full_name FROM users WHERE role = 'student' ORDER BY full_name";
$students = $conn->query($studentsQuery);

// Get all positions
$positionsQuery = "SELECT * FROM positions ORDER BY position_order";
$positions = $conn->query($positionsQuery);

// Get all candidates
$candidatesQuery = "SELECT c.id, u.full_name, u.student_id, p.position_name, p.position_order, c.status 
                    FROM candidates c 
                    JOIN users u ON c.user_id = u.id 
                    JOIN positions p ON c.position_id = p.id 
                    ORDER BY p.position_order, u.full_name";
$candidates = $conn->query($candidatesQuery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates</title>
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
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
        
        .message.warning {
            background: #fef3c7;
            color: #92400e;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
        }
        
        select:focus {
            outline: none;
            border-color: #10b981;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            font-weight: 600;
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
            padding: 8px 16px;
            font-size: 0.9em;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: #f7fafc;
            color: #10b981;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-nominated {
            background: #feebc8;
            color: #744210;
        }
        
        .badge-elected {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-lost {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .badge-ineligible {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            table {
                font-size: 0.9em;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Manage Candidates</h1>
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Nominate New Candidate</h2>
            
            <div class="info-banner">
                <strong> Important Rule:</strong> Students who have won a higher-priority position (e.g., President) cannot be nominated for lower-priority positions (e.g., Vice President). This ensures fair representation and prevents position conflicts.
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_id">Select Student</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">-- Choose Student --</option>
                        <?php
                        $students->data_seek(0);
                        while ($student = $students->fetch_assoc()):
                        ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="position_id">Select Position</label>
                    <select name="position_id" id="position_id" required>
                        <option value="">-- Choose Position --</option>
                        <?php
                        $positions->data_seek(0);
                        while ($position = $positions->fetch_assoc()):
                        ?>
                            <option value="<?php echo $position['id']; ?>">
                                Priority #<?php echo $position['position_order']; ?> - <?php echo htmlspecialchars($position['position_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" name="nominate" class="btn btn-primary"> Nominate Candidate</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Current Candidates</h2>
            
            <?php if ($candidates->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Position</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($candidate = $candidates->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($candidate['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['position_name']); ?></td>
                                <td>#<?php echo $candidate['position_order']; ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo $candidate['status']; ?>">
                                        <?php echo ucfirst($candidate['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?remove=<?php echo $candidate['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm(' Remove this candidate?\n\nThis action cannot be undone!')">
                                         Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No candidates nominated yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>