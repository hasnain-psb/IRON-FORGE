<?php
session_start();
require_once 'db.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_member'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $plan = trim($_POST['plan']);
    
    if (empty($name) || empty($email) || empty($phone) || empty($plan)) {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'All fields are required!'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Invalid email format!'];
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, plan, status) VALUES (?, ?, ?, ?, 'Active')");
            $stmt->execute([$name, $email, $phone, $plan]);
            $_SESSION['alert'] = ['type' => 'success', 'text' => 'Registration successful! New member registered.'];
            header("Location: index.php?page=members");
            exit;
        } catch (PDOException $e) {
            $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Database error: ' . $e->getMessage()];
        }
    }
}

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    $id = intval($_GET['id']);
    $current_status = $_GET['status'];
    $new_status = ($current_status === 'Active') ? 'Inactive' : 'Active';
    
    try {
        $stmt = $pdo->prepare("UPDATE members SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Member status updated successfully!'];
        header("Location: index.php?page=members");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Error updating status: ' . $e->getMessage()];
    }
}

// Handle Delete Member
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Member record deleted successfully!'];
        header("Location: index.php?page=members");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Error deleting member: ' . $e->getMessage()];
    }
}

// Router Setup
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed_pages = ['home', 'about', 'plans', 'trainers', 'gallery', 'contact', 'register', 'members'];
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iron Forge Gym - Membership Management</title>
    <!-- Google Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --bg-color: #0b0d10;
            --card-bg: #12161c;
            --card-bg-hover: #181d25;
            --primary: #ff6600;
            --primary-rgb: 255, 102, 0;
            --primary-hover: #e05500;
            --text-main: #f0f2f5;
            --text-muted: #9ba3af;
            --border: rgba(255, 255, 255, 0.08);
            --success: #10b981;
            --danger: #ef4444;
            --font: 'Outfit', sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font);
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        /* Layout & Container */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Header & Navigation */
        header {
            background-color: rgba(11, 13, 16, 0.95);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            color: var(--primary);
        }

        nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: var(--transition);
            padding: 0.5rem 0.8rem;
            border-radius: 4px;
        }

        nav a:hover, nav a.active {
            color: var(--primary);
            background-color: rgba(255, 102, 0, 0.08);
        }

        .nav-btn {
            background-color: var(--primary);
            color: white !important;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .nav-btn:hover {
            background-color: var(--primary-hover);
        }

        /* Mobile Nav Toggle */
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-main);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Main Content */
        main {
            flex-grow: 1;
            padding: 3rem 0;
        }

        /* Typography */
        h1, h2, h3 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            text-transform: uppercase;
        }

        .section-header h2 span {
            color: var(--primary);
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1.8rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            font-family: var(--font);
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 4px;
        }

        /* Alerts */
        .alert-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1001;
            max-width: 400px;
        }

        .alert {
            background-color: var(--card-bg);
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            border-left-color: var(--success);
        }

        .alert-danger {
            border-left-color: var(--danger);
        }

        .alert-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
        }

        .alert-close:hover {
            color: var(--text-main);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            background-color: var(--bg-color);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-family: var(--font);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.15);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.3rem;
            display: none;
        }

        /* Footer */
        footer {
            background-color: #080a0d;
            border-top: 1px solid var(--border);
            padding: 3rem 0;
            color: var(--text-muted);
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-col {
            flex: 1;
            min-width: 250px;
        }

        .footer-col h4 {
            color: var(--text-main);
            margin-bottom: 1.2rem;
            font-size: 1.2rem;
        }

        .footer-col p {
            margin-bottom: 0.8rem;
        }

        .footer-socials {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .footer-socials a {
            color: var(--text-muted);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .footer-socials a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            font-size: 0.9rem;
        }

        /* PAGE: Home */
        .hero {
            background: linear-gradient(rgba(11, 13, 16, 0.8), rgba(11, 13, 16, 0.95)), url('images/gym_banner.png') no-repeat center center/cover;
            border-radius: 12px;
            padding: 6rem 3rem;
            text-align: center;
            margin-bottom: 4rem;
            border: 1px solid var(--border);
        }

        .hero h1 {
            font-size: 4rem;
            line-height: 1.1;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero p {
            font-size: 1.3rem;
            color: var(--text-muted);
            max-width: 800px;
            margin: 0 auto 2.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            padding: 2.5rem 2rem;
            border-radius: 10px;
            text-align: center;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background-color: var(--card-bg-hover);
            border-color: rgba(255, 102, 0, 0.3);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        /* PAGE: About */
        .about-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text h3 {
            font-size: 1.8rem;
            margin-bottom: 1.2rem;
        }

        .about-text p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .about-amenities {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .amenity-item {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .amenity-item i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        /* PAGE: Plans */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .plan-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .plan-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 102, 0, 0.3);
        }

        .plan-card.popular {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(255, 102, 0, 0.15);
        }

        .plan-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--primary);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .plan-title {
            font-size: 1.5rem;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1.5rem;
            line-height: 1;
        }

        .plan-price span {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .plan-features li {
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            color: var(--success);
            margin-right: 0.5rem;
        }

        .comparison-table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: var(--card-bg);
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .comparison-table th, .comparison-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .comparison-table th {
            font-weight: 700;
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.02);
        }

        .comparison-table td i.fa-check {
            color: var(--success);
        }

        .comparison-table td i.fa-times {
            color: var(--danger);
        }

        /* PAGE: Trainers */
        .trainers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2rem;
        }

        .trainer-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: var(--transition);
        }

        .trainer-card:hover {
            border-color: rgba(255, 102, 0, 0.3);
            transform: translateY(-5px);
        }

        .trainer-img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-bottom: 1px solid var(--border);
        }

        .trainer-info {
            padding: 2rem;
        }

        .trainer-name {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        .trainer-role {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .trainer-bio {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
        }

        .trainer-specialties {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .specialty-tag {
            background-color: rgba(255, 102, 0, 0.1);
            color: var(--primary);
            font-size: 0.8rem;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-weight: 500;
        }

        /* PAGE: Gallery */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            aspect-ratio: 4/3;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(11, 13, 16, 0.9), transparent);
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Lightbox for Gallery */
        #lightbox {
            display: none;
            position: fixed;
            z-index: 2000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(11, 13, 16, 0.95);
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        #lightbox img {
            max-width: 90%;
            max-height: 80%;
            border-radius: 6px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            border: 2px solid var(--border);
        }

        .close-lightbox {
            position: absolute;
            top: 30px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-lightbox:hover {
            color: var(--primary);
        }

        /* PAGE: Contact */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 3rem;
        }

        .contact-info {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            padding: 2.5rem;
            border-radius: 12px;
        }

        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .contact-method i {
            color: var(--primary);
            font-size: 1.5rem;
            margin-top: 0.2rem;
        }

        .contact-method h4 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .contact-method p {
            color: var(--text-muted);
        }

        .contact-form-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            padding: 2.5rem;
            border-radius: 12px;
        }

        /* PAGE: Register */
        .register-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            padding: 3rem;
            border-radius: 12px;
        }

        /* PAGE: Members (CRUD List) */
        .members-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            max-width: 400px;
            width: 100%;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-box .form-control {
            padding-left: 2.8rem;
        }

        .members-table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: var(--card-bg);
        }

        .members-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .members-table th, .members-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .members-table th {
            font-weight: 700;
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.02);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .members-table tr:hover {
            background-color: rgba(255, 255, 255, 0.01);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-inactive {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .action-links {
            display: flex;
            gap: 0.8rem;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.95rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .action-btn-toggle {
            color: var(--info);
        }

        .action-btn-toggle:hover {
            color: #60a5fa;
        }

        .action-btn-delete {
            color: var(--danger);
        }

        .action-btn-delete:hover {
            color: #f87171;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .about-wrapper, .contact-wrapper {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .nav-toggle {
                display: block;
            }

            nav {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 80px;
                left: 0;
                width: 100%;
                background-color: var(--bg-color);
                border-bottom: 1px solid var(--border);
                padding: 1.5rem 0;
                gap: 1rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            }

            nav.open {
                display: flex;
            }

            nav a {
                width: 90%;
                text-align: center;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .hero {
                padding: 4rem 1.5rem;
            }

            .register-container {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header & Navigation -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-dumbbell text-primary"></i> IRON <span>FORGE</span>
            </a>
            
            <button class="nav-toggle" id="navToggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav id="navbar">
                <a href="index.php?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">Home</a>
                <a href="index.php?page=about" class="<?php echo $page === 'about' ? 'active' : ''; ?>">About</a>
                <a href="index.php?page=plans" class="<?php echo $page === 'plans' ? 'active' : ''; ?>">Plans</a>
                <a href="index.php?page=trainers" class="<?php echo $page === 'trainers' ? 'active' : ''; ?>">Trainers</a>
                <a href="index.php?page=gallery" class="<?php echo $page === 'gallery' ? 'active' : ''; ?>">Gallery</a>
                <a href="index.php?page=contact" class="<?php echo $page === 'contact' ? 'active' : ''; ?>">Contact</a>
                <a href="index.php?page=members" class="<?php echo $page === 'members' ? 'active' : ''; ?>">Members List</a>
                <a href="index.php?page=register" class="nav-btn">Join Now</a>
            </nav>
        </div>
    </header>

    <!-- Success/Error Notifications -->
    <div class="alert-container">
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?>">
                <span><?php echo $_SESSION['alert']['text']; ?></span>
                <button class="alert-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
    </div>

    <!-- Main Content Dynamic Container -->
    <main class="container">
        
        <?php if ($page === 'home'): ?>
            <!-- HOME PAGE -->
            <section class="hero">
                <h1>Forge Your <span>Best Self</span></h1>
                <p>Welcome to Iron Forge Gym. Access elite coaches, premium iron work gears, and customized fitness tracks designed to elevate your game.</p>
                <a href="index.php?page=register" class="btn">Start Free Trial</a>
            </section>

            <section class="section-header">
                <h2>Our <span>Core Offerings</span></h2>
                <p>Equipped to satisfy high-performance athletes as well as newcomers beginning their physical transformation journey.</p>
            </section>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-weight-hanging"></i></div>
                    <h3>Top-Tier Gear</h3>
                    <p>Row after row of professional power racks, customized free weights, and pneumatic resistance platforms.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <h3>Expert Coaches</h3>
                    <p>One-on-one personal tracking and meal plan formulations with state-certified nutritionists.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-heart-pulse"></i></div>
                    <h3>Cardio Suite</h3>
                    <p>Clean space loaded with dynamic screen treadmills, climbers, and rowing simulators overlooking the city.</p>
                </div>
            </div>

        <?php elseif ($page === 'about'): ?>
            <!-- ABOUT PAGE -->
            <section class="section-header">
                <h2>Who <span>We Are</span></h2>
                <p>Established with the dream of creating an uncompromising training facility where science-backed methods rule.</p>
            </section>

            <div class="about-wrapper">
                <div class="about-text">
                    <h3>Uncompromising Standards Since 2018</h3>
                    <p>Iron Forge Gym isn't another franchise health lounge. We are a heavy training center. Built by powerlifters, runners, and trainers, our floor features zero fluff and maximum utility.</p>
                    <p>No matter if your goal is competitive lifting, weight loss, or high intensity conditioning, we have structured spaces and coaches ready to push your training blocks further.</p>
                    <a href="index.php?page=plans" class="btn">Check Membership Plans</a>
                </div>
                <div class="about-amenities">
                    <div class="amenity-item">
                        <i class="fa-solid fa-shower"></i>
                        <div>
                            <h4>Hot Showers</h4>
                            <p>Private locker spaces.</p>
                        </div>
                    </div>
                    <div class="amenity-item">
                        <i class="fa-solid fa-whiskey-glass"></i>
                        <div>
                            <h4>Protein Bar</h4>
                            <p>Premium pre/post shakes.</p>
                        </div>
                    </div>
                    <div class="amenity-item">
                        <i class="fa-solid fa-clock"></i>
                        <div>
                            <h4>24/7 VIP Access</h4>
                            <p>Train on your schedule.</p>
                        </div>
                    </div>
                    <div class="amenity-item">
                        <i class="fa-solid fa-wifi"></i>
                        <div>
                            <h4>Gigabit Wi-Fi</h4>
                            <p>Stream music uninterrupted.</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'plans'): ?>
            <!-- PLANS PAGE -->
            <section class="section-header">
                <h2>Membership <span>Plans</span></h2>
                <p>Select a membership tier suited to your budget and training level. Cancel or upgrade anytime.</p>
            </section>

            <div class="plans-grid">
                <!-- Basic Plan -->
                <div class="plan-card">
                    <h3 class="plan-title">Basic</h3>
                    <div class="plan-price">$29<span>/mo</span></div>
                    <ul class="plan-features">
                        <li><i class="fa-solid fa-check"></i> Standard weights area access</li>
                        <li><i class="fa-solid fa-check"></i> Locker and shower access</li>
                        <li><i class="fa-solid fa-check"></i> Standard gym hours</li>
                        <li><i class="fa-solid fa-xmark" style="color: var(--danger)"></i> Group classes excluded</li>
                        <li><i class="fa-solid fa-xmark" style="color: var(--danger)"></i> Personal coaching excluded</li>
                    </ul>
                    <a href="index.php?page=register&plan=Basic" class="btn btn-outline">Select Plan</a>
                </div>
                <!-- Standard Plan -->
                <div class="plan-card popular">
                    <span class="plan-badge">Popular</span>
                    <h3 class="plan-title">Standard</h3>
                    <div class="plan-price">$49<span>/mo</span></div>
                    <ul class="plan-features">
                        <li><i class="fa-solid fa-check"></i> Standard weights & cardio</li>
                        <li><i class="fa-solid fa-check"></i> Locker and shower access</li>
                        <li><i class="fa-solid fa-check"></i> Unlimited group fitness classes</li>
                        <li><i class="fa-solid fa-check"></i> 1 Personal trainer assessment</li>
                        <li><i class="fa-solid fa-xmark" style="color: var(--danger)"></i> 24/7 Access excluded</li>
                    </ul>
                    <a href="index.php?page=register&plan=Standard" class="btn">Select Plan</a>
                </div>
                <!-- Premium Plan -->
                <div class="plan-card">
                    <h3 class="plan-title">Premium</h3>
                    <div class="plan-price">$79<span>/mo</span></div>
                    <ul class="plan-features">
                        <li><i class="fa-solid fa-check"></i> Complete 24/7 VIP keycard access</li>
                        <li><i class="fa-solid fa-check"></i> Locker, shower & sauna access</li>
                        <li><i class="fa-solid fa-check"></i> All group and yoga classes</li>
                        <li><i class="fa-solid fa-check"></i> 1 Personal coaching hour/week</li>
                        <li><i class="fa-solid fa-check"></i> Free protein shake per day</li>
                    </ul>
                    <a href="index.php?page=register&plan=Premium" class="btn btn-outline">Select Plan</a>
                </div>
            </div>

            <section class="section-header">
                <h2>Plan <span>Comparison</span></h2>
            </section>

            <div class="comparison-table-wrapper">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Basic</th>
                            <th>Standard</th>
                            <th>Premium</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Weight Area Access</td>
                            <td><i class="fa-solid fa-check"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                        </tr>
                        <tr>
                            <td>Locker & Showers</td>
                            <td><i class="fa-solid fa-check"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                        </tr>
                        <tr>
                            <td>Group Classes</td>
                            <td><i class="fa-solid fa-times"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                        </tr>
                        <tr>
                            <td>Sauna Access</td>
                            <td><i class="fa-solid fa-times"></i></td>
                            <td><i class="fa-solid fa-times"></i></td>
                            <td><i class="fa-solid fa-check"></i></td>
                        </tr>
                        <tr>
                            <td>Personal Coaching</td>
                            <td><i class="fa-solid fa-times"></i></td>
                            <td><i class="fa-solid fa-times"></i> (1 assessment)</td>
                            <td><i class="fa-solid fa-check"></i> (Weekly)</td>
                        </tr>
                        <tr>
                            <td>Hours</td>
                            <td>Standard</td>
                            <td>Standard</td>
                            <td>24/7 Keycard</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page === 'trainers'): ?>
            <!-- TRAINERS PAGE -->
            <section class="section-header">
                <h2>Our Elite <span>Trainers</span></h2>
                <p>Work with state-accredited strength coaches and recovery specialists focused on maximizing your results.</p>
            </section>

            <div class="trainers-grid">
                <div class="trainer-card">
                    <img src="images/trainer_male.png" alt="Coach Alex" class="trainer-img">
                    <div class="trainer-info">
                        <h3 class="trainer-name">Coach Alex Stone</h3>
                        <div class="trainer-role">Head Strength & Conditioning Coach</div>
                        <p class="trainer-bio">With over 8 years coaching competitive powerlifters and athletes, Alex specializes in biomechanics and barbell rehabilitation.</p>
                        <div class="trainer-specialties">
                            <span class="specialty-tag">Powerlifting</span>
                            <span class="specialty-tag">Barbell Training</span>
                            <span class="specialty-tag">Injury Rehab</span>
                        </div>
                    </div>
                </div>

                <div class="trainer-card">
                    <img src="images/trainer_female.png" alt="Coach Sarah" class="trainer-img">
                    <div class="trainer-info">
                        <h3 class="trainer-name">Coach Sarah Jenkins</h3>
                        <div class="trainer-role">Advanced Nutritionist & Mobility Coach</div>
                        <p class="trainer-bio">Sarah designs functional mobility programs alongside clean macronutrient meal paths. She helps clients sustain healthy long-term body recompositions.</p>
                        <div class="trainer-specialties">
                            <span class="specialty-tag">Mobility & Yoga</span>
                            <span class="specialty-tag">Fat Loss</span>
                            <span class="specialty-tag">Keto/Macros Plans</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'gallery'): ?>
            <!-- GALLERY PAGE -->
            <section class="section-header">
                <h2>Training Floor <span>Gallery</span></h2>
                <p>Take a look inside our clean, well-lit workout facility. Built with performance in mind.</p>
            </section>

            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="images/gym_banner.png" alt="Iron Forge Facility" class="gallery-img">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Iron Forge Facility Layout</div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="images/gallery_weights.png" alt="Strength Section" class="gallery-img">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Dumbbell Rack & Strength Deck</div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="images/gallery_cardio.png" alt="Cardio & Conditioning Room" class="gallery-img">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Treadmills & Conditioning Zone</div>
                    </div>
                </div>
            </div>

            <!-- Simple Lightbox Modal -->
            <div id="lightbox">
                <span class="close-lightbox"><i class="fa-solid fa-xmark"></i></span>
                <img id="lightboxImg" src="" alt="Enlarged Gym View">
            </div>

        <?php elseif ($page === 'contact'): ?>
            <!-- CONTACT PAGE -->
            <section class="section-header">
                <h2>Contact <span>Us</span></h2>
                <p>Have questions about plans, keys, or corporate memberships? Drop us a note or drop by the gym floor.</p>
            </section>

            <div class="contact-wrapper">
                <div class="contact-info">
                    <div class="contact-method">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <h4>Location</h4>
                            <p>128 Iron Avenue, Suite 4<br>Metro City, MC 90210</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <h4>Call Us</h4>
                            <p>+1 (555) 987-6543</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <h4>Email Support</h4>
                            <p>support@ironforge.com</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <i class="fa-solid fa-clock"></i>
                        <div>
                            <h4>Hours of Operation</h4>
                            <p>Mon - Fri: 5:00 AM - 11:00 PM<br>Sat - Sun: 7:00 AM - 9:00 PM</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form-card">
                    <h3>Send Us an Inquiry</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem;">We reply to general inquiries within 24 hours.</p>
                    
                    <form id="contactForm" onsubmit="event.preventDefault(); handleContactSubmit();">
                        <div class="form-group">
                            <label for="cname">Name</label>
                            <input type="text" id="cname" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label for="cemail">Email</label>
                            <input type="email" id="cemail" class="form-control" placeholder="your.email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="cmessage">Message</label>
                            <textarea id="cmessage" rows="5" class="form-control" placeholder="What can we help you with?" required></textarea>
                        </div>
                        <button type="submit" class="btn" style="width: 100%;">Send Inquiry</button>
                    </form>
                    <div id="contactSuccess" style="display:none; color: var(--success); margin-top: 1rem; text-align: center; font-weight: 600;">
                        <i class="fa-solid fa-circle-check"></i> Inquiry received! We will reach out shortly.
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'register'): ?>
            <!-- REGISTRATION PAGE -->
            <section class="section-header">
                <h2>Join <span>The Gym</span></h2>
                <p>Register online to pre-book your membership. Fill in your details below to get started immediately.</p>
            </section>

            <div class="register-container">
                <form id="registerForm" action="index.php?page=register" method="POST">
                    <input type="hidden" name="register_member" value="1">
                    
                    <!-- jQuery Form Alerts -->
                    <div id="formValidationError" class="alert alert-danger" style="display:none; margin-bottom: 1.5rem; justify-content: flex-start;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span id="validationErrorText"></span>
                    </div>

                    <div class="form-group">
                        <label for="regName">Full Name</label>
                        <input type="text" name="name" id="regName" class="form-control" placeholder="e.g. John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        <div class="error-message" id="nameError">Name must be at least 3 characters.</div>
                    </div>

                    <div class="form-group">
                        <label for="regEmail">Email Address</label>
                        <input type="text" name="email" id="regEmail" class="form-control" placeholder="e.g. john@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div class="error-message" id="emailError">Please enter a valid email address.</div>
                    </div>

                    <div class="form-group">
                        <label for="regPhone">Phone Number</label>
                        <input type="text" name="phone" id="regPhone" class="form-control" placeholder="e.g. 123-456-7890" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <div class="error-message" id="phoneError">Enter a valid phone number.</div>
                    </div>

                    <div class="form-group">
                        <label for="regPlan">Membership Plan</label>
                        <select name="plan" id="regPlan" class="form-control">
                            <?php 
                            $selected_plan = isset($_GET['plan']) ? $_GET['plan'] : (isset($_POST['plan']) ? $_POST['plan'] : 'Standard');
                            ?>
                            <option value="" disabled>-- Select a Plan --</option>
                            <option value="Basic" <?php echo $selected_plan === 'Basic' ? 'selected' : ''; ?>>Basic - $29/mo</option>
                            <option value="Standard" <?php echo $selected_plan === 'Standard' ? 'selected' : ''; ?>>Standard - $49/mo</option>
                            <option value="Premium" <?php echo $selected_plan === 'Premium' ? 'selected' : ''; ?>>Premium - $79/mo</option>
                        </select>
                        <div class="error-message" id="planError">Please select a plan.</div>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Complete Registration</button>
                </form>
            </div>

        <?php elseif ($page === 'members'): ?>
            <!-- MEMBERS LIST (CRUD) -->
            <section class="section-header">
                <h2>Active <span>Members</span></h2>
                <p>Manage, search, toggle status, or remove member profiles from the gym database.</p>
            </section>

            <div class="members-actions">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="memberSearch" class="form-control" placeholder="Search members by name, email or phone...">
                </div>
                <a href="index.php?page=register" class="btn"><i class="fa-solid fa-user-plus"></i> Add New Member</a>
            </div>

            <div class="members-table-wrapper">
                <table class="members-table" id="membersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Registered At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM members ORDER BY id DESC");
                            $members = $stmt->fetchAll();
                            
                            if (count($members) > 0) {
                                foreach ($members as $member) {
                                    $status_badge = ($member['status'] === 'Active') ? 'badge-active' : 'badge-inactive';
                                    echo "<tr class='member-row'>";
                                    echo "<td>" . htmlspecialchars($member['id']) . "</td>";
                                    echo "<td class='m-name'>" . htmlspecialchars($member['name']) . "</td>";
                                    echo "<td class='m-email'>" . htmlspecialchars($member['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($member['phone']) . "</td>";
                                    echo "<td><span class='badge' style='background-color: rgba(255,102,0,0.15); color: var(--primary);'>" . htmlspecialchars($member['plan']) . "</span></td>";
                                    echo "<td><span class='badge {$status_badge}'>" . htmlspecialchars($member['status']) . "</span></td>";
                                    echo "<td>" . date('Y-m-d H:i', strtotime($member['created_at'])) . "</td>";
                                    echo "<td>";
                                    echo "<div class='action-links'>";
                                    echo "<a href='index.php?page=members&action=toggle_status&id=" . $member['id'] . "&status=" . urlencode($member['status']) . "' class='action-btn action-btn-toggle' title='Toggle Active Status'><i class='fa-solid fa-rotate'></i> Status</a>";
                                    echo "<a href='index.php?page=members&action=delete&id=" . $member['id'] . "' class='action-btn action-btn-delete' onclick=\"return confirm('Are you sure you want to delete member: " . htmlspecialchars($member['name'], ENT_QUOTES) . "?');\" title='Delete Member'><i class='fa-solid fa-trash'></i> Delete</a>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' style='text-align: center; color: var(--text-muted); padding: 2rem;'>No member accounts registered yet.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='8' style='text-align: center; color: var(--danger);'>Error querying database: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-content">
            <div class="footer-col">
                <h4><i class="fa-solid fa-dumbbell text-primary" style="color: var(--primary)"></i> IRON FORGE</h4>
                <p>A training floor built with premium utilities for power, stamina, and recovery. Join our squad today and start forging the best version of yourself.</p>
                <div class="footer-socials">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Hours</h4>
                <p>Weekdays: 5:00 AM - 11:00 PM</p>
                <p>Weekends: 7:00 AM - 9:00 PM</p>
                <p>VIP Members: 24/7 Gym Access</p>
            </div>
            <div class="footer-col">
                <h4>Inquiries</h4>
                <p><i class="fa-solid fa-phone" style="margin-right: 0.5rem;"></i> +1 (555) 987-6543</p>
                <p><i class="fa-solid fa-envelope" style="margin-right: 0.5rem;"></i> support@ironforge.com</p>
                <p><i class="fa-solid fa-location-dot" style="margin-right: 0.5rem;"></i> 128 Iron Ave, Metro City</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Iron Forge Gym. All rights reserved. Designed for Web Technology Lab Submission.</p>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripting (jQuery & Native JS) -->
    <script>
        $(document).ready(function() {
            // Hamburger menu toggle for mobile
            $('#navToggle').click(function() {
                $('#navbar').toggleClass('open');
            });

            // Auto-fadeout success/error notifications after 3 seconds
            if ($('.alert').length > 0) {
                setTimeout(function() {
                    $('.alert').fadeOut('slow');
                }, 3500);
            }

            // Close notification manual trigger
            $('.alert-close').click(function() {
                $(this).parent('.alert').fadeOut('fast');
            });

            // Lightbox Gallery Show/Hide Effects
            $('.gallery-item').click(function() {
                var imgSrc = $(this).find('img').attr('src');
                $('#lightboxImg').attr('src', imgSrc);
                $('#lightbox').fadeIn(300).css('display', 'flex');
            });

            $('#lightbox, .close-lightbox').click(function(e) {
                // Ensure lightbox closes only when background or close button is clicked
                if (e.target.id === 'lightbox' || $(e.target).closest('.close-lightbox').length > 0) {
                    $('#lightbox').fadeOut(200);
                }
            });

            // Dynamic search/filter in Members List
            $('#memberSearch').on('keyup', function() {
                var query = $(this).val().toLowerCase();
                
                $('#membersTable tbody tr.member-row').filter(function() {
                    var matchName = $(this).find('.m-name').text().toLowerCase().indexOf(query) > -1;
                    var matchEmail = $(this).find('.m-email').text().toLowerCase().indexOf(query) > -1;
                    $(this).toggle(matchName || matchEmail);
                });
            });

            // Form validation (jQuery client-side checks)
            $('#registerForm').on('submit', function(e) {
                var isValid = true;
                
                var name = $('#regName').val().trim();
                var email = $('#regEmail').val().trim();
                var phone = $('#regPhone').val().trim();
                var plan = $('#regPlan').val();

                // Reset error states
                $('.error-message').hide();
                $('#formValidationError').hide();
                $('.form-control').css('border-color', 'var(--border)');

                // Name validation
                if (name === "" || name.length < 3) {
                    $('#regName').css('border-color', 'var(--danger)');
                    $('#nameError').fadeIn();
                    isValid = false;
                }

                // Email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email === "" || !emailRegex.test(email)) {
                    $('#regEmail').css('border-color', 'var(--danger)');
                    $('#emailError').fadeIn();
                    isValid = false;
                }

                // Phone validation
                var phoneRegex = /^[0-9\-\s\+\(\)]{7,15}$/;
                if (phone === "" || !phoneRegex.test(phone)) {
                    $('#regPhone').css('border-color', 'var(--danger)');
                    $('#phoneError').fadeIn();
                    isValid = false;
                }

                // Plan validation
                if (plan === null || plan === "") {
                    $('#regPlan').css('border-color', 'var(--danger)');
                    $('#planError').fadeIn();
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    $('#validationErrorText').text('Please correct the highlighted fields before submitting.');
                    $('#formValidationError').slideDown(200).css('display', 'flex');
                }
            });
        });

        // Contact Form simple animation without reloading page
        function handleContactSubmit() {
            $('#contactForm').slideUp(300, function() {
                $('#contactSuccess').fadeIn(300);
            });
        }
    </script>
</body>
</html>
