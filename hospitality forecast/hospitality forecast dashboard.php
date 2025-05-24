<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$loggedInUser = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>The Hospitality Forecast Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      /* Vibrant Color Palette */
      --primary: #FF6B6B;
      --secondary: #4ECDC4;
      --accent: #FFE66D;
      --dark: #292F36;
      --light: #F7FFF7;
      --purple: #6B5B95;
      --orange: #FF9F1C;
      --text-dark: #2D3436;
      --text-light: #F7FFF7;
      
      /* Theme variables */
      --bg-color: var(--light);
      --text-color: var(--text-dark);
      --card-bg: white;
      --sidebar-bg: var(--dark);
      --welcome-bg: linear-gradient(135deg, var(--purple) 0%, var(--primary) 100%);
    }

    [data-theme="dark"] {
      --bg-color: var(--dark);
      --text-color: var(--text-light);
      --card-bg: #3A4048;
      --sidebar-bg: #1E1E24;
      --welcome-bg: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body {
      display: flex;
      height: 100vh;
      overflow: hidden;
      background: var(--bg-color);
      color: var(--text-color);
    }

    /* Theme Toggle Button */
    .theme-toggle {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--primary);
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      z-index: 1000;
    }

    .sidebar {
      width: 320px;
      background: var(--sidebar-bg);
      color: white;
      display: flex;
      flex-direction: column;
      padding: 30px;
      box-shadow: 8px 0 25px rgba(0,0,0,0.15);
      z-index: 10;
      position: relative;
      overflow: hidden;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(255,107,107,0.1) 0%, transparent 100%);
    }

    .sidebar h2 {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      margin-bottom: 40px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(78,205,196,0.3);
      position: relative;
      color: var(--secondary);
    }

    .sidebar h2::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 70px;
      height: 2px;
      background: var(--secondary);
    }

    .nav {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .nav button, .nav .nav-link-button {
      background: rgba(255,255,255,0.08);
      border: none;
      color: white;
      padding: 18px 25px;
      margin: 5px 0;
      cursor: pointer;
      font-size: 17px;
      border-radius: 10px;
      text-align: left;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      font-weight: 500;
      letter-spacing: 0.5px;
      text-decoration: none;
      width: 100%;
    }

    .nav button::before, .nav .nav-link-button::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 5px;
      height: 100%;
      background: var(--accent);
      transform: scaleY(0);
      transform-origin: top;
      transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .nav button:hover, .nav .nav-link-button:hover {
      background: rgba(255,230,109,0.15);
      transform: translateX(10px);
      color: var(--accent);
    }

    .nav button:hover::before, .nav .nav-link-button:hover::before {
      transform: scaleY(1);
    }

    .nav button i, .nav .nav-link-button i {
      margin-right: 15px;
      font-size: 22px;
      min-width: 25px;
    }

    .main-content {
      flex-grow: 1;
      padding: 40px;
      background-color: var(--bg-color);
      overflow-y: auto;
      position: relative;
    }

    .section {
      display: none;
      animation: fadeIn 0.6s ease;
      max-width: 1200px;
      margin: 0 auto;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .active {
      display: block;
    }

    .logout-btn {
      position: fixed;
      top: 30px;
      right: 40px;
      background: linear-gradient(135deg, var(--primary) 0%, #FF8E8E 100%);
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      box-shadow: 0 5px 20px rgba(255,107,107,0.3);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      z-index: 100;
      letter-spacing: 0.5px;
    }

    .logout-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255,107,107,0.4);
      background: linear-gradient(135deg, #FF8E8E 0%, var(--primary) 100%);
    }

    .logout-btn i {
      margin-right: 10px;
      font-size: 16px;
    }

    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 52px;
      color: var(--text-color);
      margin-bottom: 30px;
      position: relative;
      display: inline-block;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    h1::after {
      content: '';
      position: absolute;
      bottom: -12px;
      left: 0;
      width: 80px;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      border-radius: 3px;
    }

    h2 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      color: var(--secondary);
      margin: 40px 0 25px;
      font-weight: 600;
    }

    p, li {
      font-size: 18px;
      color: var(--text-color);
      line-height: 1.8;
      margin-bottom: 20px;
    }

    /* Enhanced Member Cards */
    .member-card {
      display: flex;
      align-items: center;
      margin-bottom: 40px;
      background: var(--card-bg);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border-left: 8px solid var(--primary);
      position: relative;
      overflow: hidden;
    }

    .member-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(255,107,107,0.03) 0%, transparent 100%);
    }

    .member-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }

    .member-card img {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      margin-right: 40px;
      object-fit: cover;
      border: 6px solid var(--primary);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      transition: all 0.4s ease;
    }

    .member-card:hover img {
      transform: scale(1.05);
      border-color: var(--secondary);
    }

    .member-info {
      flex: 1;
      position: relative;
      z-index: 2;
    }

    .member-card strong {
      font-size: 28px;
      color: var(--text-color);
      display: block;
      margin-bottom: 12px;
      font-weight: 700;
    }

    .member-role {
      font-size: 20px;
      color: var(--accent);
      font-weight: 600;
      margin-bottom: 20px;
      display: inline-block;
      padding: 8px 20px;
      background: rgba(255,230,109,0.15);
      border-radius: 30px;
      border: 2px solid var(--accent);
    }

    ul {
      padding-left: 25px;
      margin: 25px 0;
    }

    li {
      margin-bottom: 15px;
      position: relative;
      padding-left: 30px;
      font-size: 18px;
    }

    li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 12px;
      width: 12px;
      height: 12px;
      background: var(--primary);
      border-radius: 50%;
    }

    /* Enhanced Welcome Container */
    .welcome-container {
      background: var(--welcome-bg);
      color: white;
      padding: 70px;
      border-radius: 25px;
      margin-bottom: 50px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
    }

    .welcome-container::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,230,109,0.1) 0%, transparent 70%);
    }

    .welcome-container h1 {
      color: white;
      margin-bottom: 25px;
      font-size: 60px;
      position: relative;
      z-index: 2;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .welcome-container h1::after {
      background: var(--accent);
    }

    .welcome-container p {
      color: rgba(255,255,255,0.9);
      font-size: 22px;
      max-width: 800px;
      position: relative;
      z-index: 2;
      line-height: 1.9;
    }

    /* Feature Box Enhancements */
    .feature-box {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      margin: 40px 0;
    }

    .feature-card {
      flex: 1 1 300px;
      background: var(--card-bg);
      padding: 35px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border-top: 6px solid var(--primary);
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .feature-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 0 25px 50px rgba(0,0,0,0.15);
    }

    .feature-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: var(--secondary);
      margin-bottom: 20px;
      position: relative;
      z-index: 2;
    }

    .feature-icon {
      font-size: 50px;
      color: var(--primary);
      margin-bottom: 25px;
      transition: all 0.4s ease;
      position: relative;
      z-index: 2;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.2);
      color: var(--secondary);
    }

    .dashboard-intro {
      margin-bottom: 40px;
    }

    .dashboard-intro p {
      font-size: 20px;
      max-width: 800px;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script>
    function showSection(id) {
      const sections = document.querySelectorAll('.section');
      sections.forEach(sec => sec.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      window.scrollTo(0, 0);
    }

    function toggleTheme() {
      const body = document.body;
      const currentTheme = body.getAttribute('data-theme');
      
      if (currentTheme === 'dark') {
        body.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
      } else {
        body.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
      }
    }

    // Check for saved theme preference
    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('theme') || 'light';
      if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
      }
      
      showSection('title');
    });

    window.onbeforeunload = null;
  </script>
</head>
<body>
  <button class="theme-toggle" onclick="toggleTheme()">
    <i class="fas fa-moon"></i>
  </button>

  <div class="sidebar">
    <h2>Hospitality Forecast</h2>
    <div class="nav">
      <button onclick="showSection('title')">
        <i class="fas fa-hotel"></i> Dashboard Home
      </button>
      <button onclick="showSection('description')">
        <i class="fas fa-book-open"></i> Project Details
      </button>
      <button onclick="showSection('about')">
        <i class="fas fa-users-crown"></i> Our Team
      </button>
      <a href="https://us3.ca.analytics.ibm.com/bi/?perspective=dashboard&pathRef=.my_folders%2Fbi%2Bproject1&action=view&mode=dashboard&subView=model00000196b3cfc352_00000000" 
         class="nav-link-button">
        <i class="fas fa-chart-pie"></i> Live Analytics
      </a>
    </div>
  </div>

  <div class="main-content">
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> LOGOUT
      </button>
    </form>

    <div id="title" class="section active">
      <div class="welcome-container">
        <h1>The Hospitality Forecast</h1>
        <p>Experience the future of hotel management with our premium analytics dashboard. Leverage powerful data insights to optimize revenue, understand guest behavior, and stay ahead of market trends in the competitive hospitality industry.</p>
      </div>
      
      <div class="feature-box">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-users"></i>
          </div>
          <h3>Guest Analytics</h3>
          <p>Comprehensive insights into guest demographics, preferences, and booking patterns to enhance customer experiences.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3>Revenue Intelligence</h3>
          <p>Advanced metrics and forecasting tools to maximize property's financial performance and occupancy rates.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-tags"></i>
          </div>
          <h3>Dynamic Pricing</h3>
          <p>price strategies and  recommendations based on historical trends with many derived metrices such as seasonal bookings, room type, etc...</p>
        </div>
      </div>
    </div>

    <div id="description" class="section">
      <h1>Project Overview</h1>
      <p>The Hospitality Forecast represents a revolutionary approach to hotel business intelligence, combining elegant design with powerful analytics capabilities.</p>
      
      <div class="feature-box">
        <div class="feature-card">
          <h3>Comprehensive Data Integration</h3>
          <p>Our solution aggregates data from multiple sources including PMS, ADR, and market attributes to provide a 360° view of your property's performance.</p>
        </div>
        
        <div class="feature-card">
          <h3>Predictive Analytics</h3>
          <p>By the usage of forecast occupancy trends, helping to make informed decisions about customer influx, pricing strategies, and revenue per room.</p>
        </div>
      </div>
      
      <h2>Key Functionality</h2>
      <ul>
        <li><strong>Analyzing Performance:</strong> By the usage of KPIs like RevPAR, ADR, and occupancy for comparing the trends.</li>
        <li><strong>Competitive Benchmarking:</strong> Compares property's performance against tagetted values and  market averages</li>
        <li><strong>Guest Segmentation:</strong> Identify high-value guest segments and tailor marketing strategies accordingly</li>
        <li><strong>Seasonal Trend Analysis:</strong> Visualize multi-year patterns to anticipate demand fluctuations</li>
        <li><strong>Various Derived metrices comparisons:</strong> visualize the trends with many derived metrices such as customer segment, room type, booking channel, hotel branch and location</li>
      </ul>
    </div>

    <div id="about" class="section">
      <h1>Meet Our Elite Team</h1>
      <p>Our team combines hospitality expertise with cutting-edge technology skills to deliver exceptional results.</p>
      
      <h2>Project Leadership</h2>
      <div class="member-card">
        <img src="images/rooba.jpg" alt="Rooba">
        <div class="member-info">
          <strong>Rooba.B</strong>
          <span class="member-role">Lead Dashboard Architect</span>
          <p>With a full dedicational and interested skill in analytics, Rooba specializes in transforming complex data into intuitive visualizations that drive decision-making at luxury hotel properties worldwide.</p>
        </div>
      </div>
      
      <h2>Creative & Technical Team</h2>
      <div class="member-card">
        <img src="images/nivetha.jpg" alt="Nivetha">
        <div class="member-info">
          <strong>Nivetha.R.T</strong>
          <span class="member-role">UX Design Director</span>
          <p>Nivetha's elegant interface designs have make our project to be elegant and improves customer experiences , combining aesthetic appeal with exceptional usability for hospitality professionals.</p>
        </div>
      </div>
      
      <div class="member-card">
        <img src="images/sujith.jpg" alt="Sujith">
        <div class="member-info">
          <strong>Sujith Kumar.P</strong>
          <span class="member-role"> Deductive Data Analyst</span>
          <p>Sujith works on data for preprocessing to neglect the errors and for elegant experience, with special expertise in forecasting for the hospitality sector to predict the future trends for business purpose.</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>