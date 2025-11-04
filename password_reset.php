<?php
require_once 'config.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$messageType = '';
$resetStudent = null;

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $userId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'Please enter and confirm the new password!';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match!';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 4) {
        $message = 'Password must be at least 4 characters long!';
        $messageType = 'error';
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update the password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'student'");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            $message = 'Password reset successfully! Student can now login with the new password.';
            $messageType = 'success';
            $resetStudent = null;
        } else {
            $message = 'Failed to reset password. Please try again.';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Load student details if requested
if (isset($_GET['reset'])) {
    $resetId = $_GET['reset'];
    $stmt = $conn->prepare("SELECT id, student_id, full_name FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $resetId);
    $stmt->execute();
    $resetStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all students
$studentsQuery = "SELECT id, student_id, full_name, email FROM users WHERE role = 'student' ORDER BY full_name";
$students = $conn->query($studentsQuery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
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
            max-width: 1000px;
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
        
        .reset-form {
            background: #f7fafc;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #10b981;
        }
        
        .student-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }
        
        .student-info strong {
            color: #10b981;
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
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
        }
        
        input:focus {
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #10b981;
            color: white;
        }
        
        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #cbd5e0;
            color: #2d3748;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #a0aec0;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
            padding: 8px 16px;
            font-size: 0.9em;
        }
        
        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-2px);
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
        
        .password-strength {
            font-size: 0.85em;
            margin-top: 5px;
            color: #666;
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
        <h1> Password Reset</h1>
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($resetStudent): ?>
        <!-- Reset Password Form -->
        <div class="card">
            <h2> Reset Password</h2>
            
            <div class="warning-banner">
                <strong> Security Notice:</strong> Make sure to inform the student of their new password securely. Consider using a temporary password that they should change after first login.
            </div>
            
            <div class="reset-form">
                <div class="student-info">
                    <strong>Student:</strong> <?php echo htmlspecialchars($resetStudent['full_name']); ?><br>
                    <strong>Student ID:</strong> <?php echo htmlspecialchars($resetStudent['student_id']); ?>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo $resetStudent['id']; ?>">
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Enter new password" required minlength="4">
                        <p class="password-strength">Minimum 4 characters required</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Re-enter new password" required minlength="4">
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-primary">
                         Reset Password
                    </button>
                    <a href="password_reset.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Student List -->
        <div class="card">
            <h2>Select Student to Reset Password</h2>
            
            <div class="info-banner">
                <strong> Admin Tool:</strong> Use this tool to reset passwords for students who have forgotten their credentials. No email verification required.
            </div>
            
            <?php if ($students->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <a href="?reset=<?php echo $student['id']; ?>" class="btn btn-warning">
                                        🔑 Reset Password
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No students registered yet.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>