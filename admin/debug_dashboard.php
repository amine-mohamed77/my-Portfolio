<?php
session_start();
require_once 'includes/auth.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

// Fetch a skill to test
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT * FROM skills WHERE id = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$skill = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #1a1a1a; color: white; padding: 20px; }
        .test-box { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1 class="text-3xl font-bold text-blue-500 mb-4">🔧 Dashboard Debug Tool</h1>

    <div class="test-box">
        <h2 class="text-xl font-bold mb-3">✅ Test 1: Database Connection</h2>
        <?php if ($skill): ?>
            <p class="success">✅ Database connected successfully!</p>
            <p>Sample skill loaded: <strong><?php echo htmlspecialchars($skill['name']); ?></strong></p>
            <p>Color in database: <strong><?php echo $skill['color'] ?? 'NULL'; ?></strong></p>
        <?php else: ?>
            <p class="error">❌ No skills found in database</p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2 class="text-xl font-bold mb-3">✅ Test 2: Color Input Fields</h2>
        <label class="block mb-2">Color Picker:</label>
        <input type="color" id="test-color" value="#3b82f6" class="h-12 w-24 rounded-lg cursor-pointer">
        
        <label class="block mb-2 mt-4">Hex Input:</label>
        <input type="text" id="test-color-hex" value="#3b82f6" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-600 text-white">
        
        <div id="color-test-result" class="mt-4 p-3 bg-gray-800 rounded-lg">
            Waiting for interaction...
        </div>
    </div>

    <div class="test-box">
        <h2 class="text-xl font-bold mb-3">✅ Test 3: Form Submission Test</h2>
        <form id="test-form" class="space-y-3">
            <input type="text" id="skill-name" value="Test Skill" class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-600 text-white">
            <input type="number" id="skill-level" value="85" class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-600 text-white">
            <input type="color" id="skill-color" value="#ef4444" class="h-12 w-24 rounded-lg cursor-pointer">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-bold">
                Test Submit
            </button>
        </form>
        <div id="form-test-result" class="mt-4 p-3 bg-gray-800 rounded-lg">
            Form not submitted yet...
        </div>
    </div>

    <div class="test-box">
        <h2 class="text-xl font-bold mb-3">✅ Test 4: API Call Test</h2>
        <button onclick="testAPIUpdate()" class="px-6 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-bold">
            Test API Update (Skill ID: 1)
        </button>
        <div id="api-test-result" class="mt-4 p-3 bg-gray-800 rounded-lg">
            API not called yet...
        </div>
    </div>

    <div class="test-box">
        <h2 class="text-xl font-bold mb-3">📋 Console Logs</h2>
        <div id="console-logs" class="bg-black p-3 rounded-lg font-mono text-sm max-h-64 overflow-y-auto">
            <div class="text-green-400">Console ready...</div>
        </div>
    </div>

    <script>
        // Capture console logs
        const consoleDiv = document.getElementById('console-logs');
        const originalLog = console.log;
        const originalError = console.error;

        console.log = function(...args) {
            originalLog.apply(console, args);
            const div = document.createElement('div');
            div.className = 'text-green-400';
            div.textContent = '> ' + args.join(' ');
            consoleDiv.appendChild(div);
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        };

        console.error = function(...args) {
            originalError.apply(console, args);
            const div = document.createElement('div');
            div.className = 'text-red-400';
            div.textContent = '❌ ' + args.join(' ');
            consoleDiv.appendChild(div);
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        };

        console.log('Debug page loaded');

        // Test 2: Color sync
        const testColor = document.getElementById('test-color');
        const testColorHex = document.getElementById('test-color-hex');
        const colorResult = document.getElementById('color-test-result');

        testColor.addEventListener('input', (e) => {
            testColorHex.value = e.target.value;
            colorResult.innerHTML = `<span class="success">✅ Color picker changed to: ${e.target.value}</span>`;
            console.log('Color picker changed:', e.target.value);
        });

        testColorHex.addEventListener('input', (e) => {
            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                testColor.value = e.target.value;
                colorResult.innerHTML = `<span class="success">✅ Hex input changed to: ${e.target.value}</span>`;
                console.log('Hex input changed:', e.target.value);
            } else {
                colorResult.innerHTML = `<span class="warning">⚠️ Invalid hex: ${e.target.value}</span>`;
            }
        });

        // Test 3: Form submission
        const testForm = document.getElementById('test-form');
        const formResult = document.getElementById('form-test-result');

        testForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const data = {
                name: document.getElementById('skill-name').value,
                level: parseInt(document.getElementById('skill-level').value),
                color: document.getElementById('skill-color').value
            };

            formResult.innerHTML = `<span class="success">✅ Form submitted!</span><pre>${JSON.stringify(data, null, 2)}</pre>`;
            console.log('Form submitted:', data);
        });

        // Test 4: API call
        async function testAPIUpdate() {
            const apiResult = document.getElementById('api-test-result');
            apiResult.innerHTML = '<span class="warning">⏳ Testing API...</span>';
            console.log('Testing API update...');

            const testData = {
                id: 1,
                name: 'Test Skill',
                level: 85,
                category: 'Frontend',
                icon_type: 'text',
                icon_value: 'TS',
                color: '#ef4444',
                is_active: 1
            };

            try {
                console.log('Sending PUT request:', testData);
                
                const response = await fetch('api/skills.php', {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(testData)
                });

                console.log('Response status:', response.status);
                
                const result = await response.json();
                console.log('Response data:', result);

                if (result.success) {
                    apiResult.innerHTML = `<span class="success">✅ API Success!</span><pre>${JSON.stringify(result, null, 2)}</pre>`;
                } else {
                    apiResult.innerHTML = `<span class="error">❌ API Error:</span><pre>${JSON.stringify(result, null, 2)}</pre>`;
                }
            } catch (error) {
                console.error('API call failed:', error);
                apiResult.innerHTML = `<span class="error">❌ API Failed: ${error.message}</span>`;
            }
        }

        console.log('All tests initialized');
    </script>

    <hr class="my-8 border-gray-700">
    
    <div class="text-center">
        <a href="dashboard.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg font-bold">
            ← Back to Dashboard
        </a>
    </div>

</body>
</html>
