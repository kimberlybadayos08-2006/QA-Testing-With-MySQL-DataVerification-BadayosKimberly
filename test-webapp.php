<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "birthday_wishnote";

$message = "";
$messageType = "";

// Establish Database Connection
$conn = new mysqli($servername,$username, $password,$dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName   = trim($_POST['FirstName'] ?? '');
    $lastName    = trim($_POST['LastName'] ?? '');
    $govPosition = trim($_POST['GovPosition'] ?? '');
    $location    = trim($_POST['Location'] ?? '');

    /* 
     * INTENTIONAL DATABASE INSERTION FLAW:
     * The form captures the 'Location' variable, but the SQL statement below 
     * intentionally omits the 'Location' column and binding parameter. 
     * Only FirstName, LastName, and GovPosition are inserted into 'main_corrupt_politicians'.
     */
    $sql = "INSERT INTO main_corrupt_politicians (FirstName, LastName, GovPosition) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sss", $firstName, $lastName,$govPosition);
        if ($stmt->execute()) {$message = "Success! The wish note has been sent.";
            $messageType = "success";
        } else {
            $message = "Error executing query: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Database Error: Could not prepare SQL statement.";
        $messageType = "error";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kara David's Wish Note</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
            padding: 30px;
        }
        h2 {
            color: #1e88e5;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .alert-success {
            background-color: #e3f2fd;
            color: #0d47a1;
            border: 1px solid #bbdefb;
        }
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #1e88e5;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #1e88e5;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-submit:hover {
            background-color: #1565c0;
        }
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .modal h3 {
            color: #1e88e5;
            margin-bottom: 15px;
        }
        .modal-data {
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-confirm {
            background-color: #1e88e5;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-confirm:hover {
            background-color: #1565c0;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Kara David's Wish Note</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form id="wishnoteForm" action="" method="POST">
        <div class="form-group">
            <label for="FirstName">Target's First Name *</label>
            <input type="text" id="FirstName" name="FirstName" maxlength="30" required>
        </div>

        <div class="form-group">
            <label for="LastName">Target's Last Name *</label>
            <input type="text" id="LastName" name="LastName" maxlength="30" required>
        </div>

        <div class="form-group">
            <label for="GovPosition">Target's Government Position *</label>
            <input type="text" id="GovPosition" name="GovPosition" maxlength="20" required>
        </div>

        <div class="form-group">
            <label for="Location">Target's Location</label>
            <input type="text" id="Location" name="Location" maxlength="40">
        </div>

        <button type="submit" class="btn-submit">Send Wish Note</button>
    </form>
</div>

<!-- Modal Dialog -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <h3>Verify Target Data</h3>
        <div class="modal-data" id="modalData"></div>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnCancel">Cancel</button>
            <button class="btn-confirm" id="btnConfirm">Confirm & Send</button>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('wishnoteForm');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalData = document.getElementById('modalData');
    const btnCancel = document.getElementById('btnCancel');
    const btnConfirm = document.getElementById('btnConfirm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const firstName = document.getElementById('FirstName').value;
        const lastName = document.getElementById('LastName').value;
        const govPosition = document.getElementById('GovPosition').value;
        const location = document.getElementById('Location').value || 'N/A';

        modalData.innerHTML = `
            <strong>First Name:</strong> ${escapeHtml(firstName)}<br>
            <strong>Last Name:</strong> ${escapeHtml(lastName)}<br>
            <strong>Government Position:</strong> ${escapeHtml(govPosition)}<br>
            <strong>Location:</strong> ${escapeHtml(location)}
        `;

        modalOverlay.style.display = 'flex';
    });

    btnCancel.addEventListener('click', function() {
        modalOverlay.style.display = 'none';
    });

    btnConfirm.addEventListener('click', function() {
        form.submit();
    });

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
</script>

</body>
</html>
