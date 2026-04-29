<!DOCTYPE html>
<html>
<head>
    <title>Debug User Profile</title>
</head>
<body>
    <h1>Debug User Profile Data</h1>
    <?php
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $user = App\Models\User::first();
    
    echo "<h2>User Data:</h2>";
    echo "<p><strong>ID:</strong> " . $user->id . "</p>";
    echo "<p><strong>Name:</strong> " . $user->name . "</p>";
    echo "<p><strong>Email:</strong> " . $user->email . "</p>";
    echo "<p><strong>Position:</strong> " . ($user->position ?? 'NULL') . "</p>";
    echo "<p><strong>Photo Path:</strong> " . ($user->profile_photo ?? 'NULL') . "</p>";
    
    if ($user->profile_photo) {
        $fullPath = __DIR__ . '/storage/app/public/' . $user->profile_photo;
        echo "<p><strong>Full Path:</strong> " . $fullPath . "</p>";
        echo "<p><strong>File Exists:</strong> " . (file_exists($fullPath) ? 'YES' : 'NO') . "</p>";
        
        if (file_exists($fullPath)) {
            echo "<h3>Preview:</h3>";
            echo "<img src='/iot-tkhs/storage/" . $user->profile_photo . "' style='width: 150px; height: 150px; border-radius: 50%; object-fit: cover;'>";
        }
    }
    
    echo "<hr>";
    echo "<h2>Storage Link Check:</h2>";
    $storageLink = __DIR__ . '/public/storage';
    echo "<p><strong>Storage Link Exists:</strong> " . (file_exists($storageLink) ? 'YES' : 'NO') . "</p>";
    
    if (file_exists($storageLink . '/profile_photos')) {
        echo "<p><strong>Profile Photos Folder:</strong> EXISTS</p>";
        $files = scandir($storageLink . '/profile_photos');
        echo "<p><strong>Files:</strong></p><ul>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.gitignore') {
                echo "<li>" . $file . "</li>";
            }
        }
        echo "</ul>";
    }
    ?>
</body>
</html>
