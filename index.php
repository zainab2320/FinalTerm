<?php
include 'config.php';
requireLogin();

$user = getCurrentUser();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $date = $conn->real_escape_string($_POST['date'] ?? '');
    $time = $conn->real_escape_string($_POST['time'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);

    if ($title && $category && $date && $time && $location && $capacity > 0) {
        $imageUrl = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop';
        
        $sql = "INSERT INTO events (title, category, date, time, location, description, capacity, image_url) 
                VALUES ('$title', '$category', '$date', '$time', '$location', '$description', $capacity, '$imageUrl')";
        
        if ($conn->query($sql) === TRUE) {
            $message = 'Event created successfully! It will be live soon.';
            $messageType = 'success';
        } else {
            $message = 'Error creating event: ' . $conn->error;
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill all required fields!';
        $messageType = 'error';
    }
}

// Fetch recent events to display
$result = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 4");
$recentEvents = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Smart Community Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="background">
        <div class="float-shape shape-1"></div>
        <div class="float-shape shape-2"></div>
        <div class="float-shape shape-3"></div>
    </div>

    <nav>
        <div class="nav-container">
            <div class="logo">🚀 SmartHub</div>
            <ul>
                <li><a href="index.php" class="active">Create Event</a></li>
                <li><a href="events.php">Browse Events</a></li>
                <li><a href="announcements.php">Announcements</a></li>
                <li><a href="register.php">My Registrations</a></li>
                <li style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--secondary);">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    <a href="logout.php" class="btn btn-small" style="margin: 0;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Create Your Event</h1>
            <p>Organize amazing community experiences and connect with people who share your interests</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" placeholder="e.g., Tech Meetup 2025" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Tech">Technology</option>
                            <option value="Art">Art & Culture</option>
                            <option value="Sports">Sports</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Education">Education</option>
                            <option value="Social">Social & Networking</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label for="date">Event Date *</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="time">Event Time *</label>
                        <input type="time" id="time" name="time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" placeholder="e.g., 123 Main Street, Downtown" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Participant Capacity *</label>
                    <input type="number" id="capacity" name="capacity" placeholder="e.g., 100" min="1" required>
                </div>

                <div class="form-group">
                    <label for="description">Event Description</label>
                    <textarea id="description" name="description" placeholder="Describe what your event is about, what attendees will learn or experience..."></textarea>
                </div>

                <button type="submit" class="btn">Create Event</button>
            </form>
        </div>

        <?php if (count($recentEvents) > 0): ?>
            <div style="margin-top: 4rem;">
                <h2 style="font-size: 2rem; margin-bottom: 2rem; text-align: center; background: linear-gradient(135deg, #7c3aed, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Recent Events</h2>
                <div class="grid">
                    <?php foreach ($recentEvents as $event): 
                        $spotsLeft = $event['capacity'] - $event['registered'];
                        $percentage = ($event['registered'] / $event['capacity']) * 100;
                    ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo $event['image_url']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                <div class="event-badge"><?php echo htmlspecialchars($event['category']); ?></div>
                            </div>
                            <div class="event-info">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-details">
                                    <div class="event-detail-row">
                                        <span class="event-detail-icon">📅</span>
                                        <span><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                                    </div>
                                    <div class="event-detail-row">
                                        <span class="event-detail-icon">⏰</span>
                                        <span><?php echo date('h:i A', strtotime($event['time'])); ?></span>
                                    </div>
                                    <div class="event-detail-row">
                                        <span class="event-detail-icon">📍</span>
                                        <span><?php echo substr(htmlspecialchars($event['location']), 0, 40); ?></span>
                                    </div>
                                </div>
                                <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 120); ?>...</p>
                                <div class="capacity-section">
                                    <div class="capacity-text"><?php echo $spotsLeft > 0 ? $spotsLeft . ' spots available' : 'Sold Out'; ?></div>
                                    <div class="capacity-bar">
                                        <div class="capacity-fill" style="width: <?php echo min($percentage, 100); ?>%;"></div>
                                    </div>
                                </div>
                                <div class="event-footer">
                                    <span style="color: rgba(226, 232, 240, 0.6); font-size: 0.9rem;"><?php echo $event['registered']; ?> registered</span>
                                    <a href="register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-small">Register</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>