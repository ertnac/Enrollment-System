<?php
$pageTitle = "Pending Enrollment Applications";
include 'header.php';
?>



<div class="search-filter">
    <div class="search-box">
        <input type="text" placeholder="Search pending applications...">
    </div>
    <button class="btn btn-secondary">
        <i data-lucide="filter"></i> Filter
    </button>
    <button class="btn">
        <i data-lucide="download"></i> Export
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>APP-2023-101</td>
                <td>David Kim</td>
                <td>Computer Science</td>
                <td>2nd Year</td>
                <td>June 1, 2023</td>
                <td><span class="status pending">Pending Review</span></td>
                <td>
                    <button class="action-btn" title="Approve"><i data-lucide="check"></i></button>
                    <button class="action-btn" title="Reject"><i data-lucide="x"></i></button>
                    <button class="action-btn" title="View Details"><i data-lucide="eye"></i></button>
                </td>
            </tr>
            <tr>
                <td>APP-2023-102</td>
                <td>Sophia Martinez</td>
                <td>Business Administration</td>
                <td>1st Year</td>
                <td>June 2, 2023</td>
                <td><span class="status pending">Documents Needed</span></td>
                <td>
                    <button class="action-btn" title="Approve"><i data-lucide="check"></i></button>
                    <button class="action-btn" title="Reject"><i data-lucide="x"></i></button>
                    <button class="action-btn" title="View Details"><i data-lucide="eye"></i></button>
                </td>
            </tr>
            <tr>
                <td>APP-2023-103</td>
                <td>Ryan Chen</td>
                <td>Electrical Engineering</td>
                <td>3rd Year</td>
                <td>June 3, 2023</td>
                <td><span class="status pending">Pending Payment</span></td>
                <td>
                    <button class="action-btn" title="Approve"><i data-lucide="check"></i></button>
                    <button class="action-btn" title="Reject"><i data-lucide="x"></i></button>
                    <button class="action-btn" title="View Details"><i data-lucide="eye"></i></button>
                </td>
            </tr>
            <tr>
                <td>APP-2023-104</td>
                <td>Emma Wilson</td>
                <td>Psychology</td>
                <td>1st Year</td>
                <td>June 5, 2023</td>
                <td><span class="status pending">Under Review</span></td>
                <td>
                    <button class="action-btn" title="Approve"><i data-lucide="check"></i></button>
                    <button class="action-btn" title="Reject"><i data-lucide="x"></i></button>
                    <button class="action-btn" title="View Details"><i data-lucide="eye"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>