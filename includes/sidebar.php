<aside class="sidebar">
    <div class="logo">
        <i data-lucide="school"></i>
        <span>EduTrack</span>
    </div>
    <nav class="nav-menu">
        <a href="?view=dashboard" class="nav-item <?php echo ($view == 'dashboard') ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <a href="?view=students" class="nav-item <?php echo ($view == 'students') ? 'active' : ''; ?>">
            <i data-lucide="users"></i>
            <span>Students</span>
        </a>
        <a href="?view=instructors" class="nav-item <?php echo ($view == 'instructors') ? 'active' : ''; ?>">
            <i data-lucide="user-cog"></i>
            <span>Instructors</span>
        </a>
        <a href="?view=enroll" class="nav-item <?php echo ($view == 'enroll') ? 'active' : ''; ?>">
            <i data-lucide="book-open"></i>
            <span>Enroll Students</span>
        </a>
        <a href="?view=pending-enrollees" class="nav-item <?php echo     ($view == 'pending-enrollees') ? 'active' : ''; ?>">
            <i data-lucide="clock"></i>
            <span>Pending Enrollees</span>
        </a>
        <a href="?view=programs" class="nav-item <?php echo ($view == 'programs') ? 'active' : ''; ?>">
            <i data-lucide="layers"></i>
            <span>Programs & Courses</span>
        </a>
    </nav>
</aside>