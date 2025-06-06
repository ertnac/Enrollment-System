<?php
$pageTitle = "Dashboard";
include 'header.php';
?>
<!-- Stats Cards -->
<div class="card-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Total Students</h3>
            <div class="card-icon">
                <i data-lucide="users"></i>
            </div>
        </div>
        <div class="card-value">1,248</div>
        <p class="card-desc">12% increase from last month</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Active Courses</h3>
            <div class="card-icon">
                <i data-lucide="book-open"></i>
            </div>
        </div>
        <div class="card-value">56</div>
        <p class="card-desc">Across 8 departments</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Instructors</h3>
            <div class="card-icon">
                <i data-lucide="user-cog"></i>
            </div>
        </div>
        <div class="card-value">84</div>
        <p class="card-desc">32 full-time, 52 part-time</p>
    </div>
</div>

<!-- Recent Enrollments Table -->
<div class="table-container">
    <h2 class="card-title" style="margin-bottom: 1rem;">Recent Enrollments</h2>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Date Enrolled</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>S2023-001</td>
                <td>Alex Johnson</td>
                <td>Computer Science</td>
                <td>May 15, 2023</td>
                <td><span class="status active">Active</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
            <tr>
                <td>S2023-002</td>
                <td>Maria Garcia</td>
                <td>Business Administration</td>
                <td>May 16, 2023</td>
                <td><span class="status active">Active</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
            <tr>
                <td>S2023-003</td>
                <td>James Wilson</td>
                <td>Electrical Engineering</td>
                <td>May 17, 2023</td>
                <td><span class="status inactive">Pending</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>