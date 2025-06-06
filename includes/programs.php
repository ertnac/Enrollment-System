<?php
$pageTitle = "Programs & Courses";
include 'header.php';
?>


<div class="search-filter">
    <div class="search-box">
        <input type="text" placeholder="Search programs or courses...">
    </div>
    <button class="btn">
        <i data-lucide="plus"></i> Add Program
    </button>
    <button class="btn">
        <i data-lucide="plus"></i> Add Course
    </button>
</div>

<div class="table-container">
    <h2 style="margin-bottom: 1rem;">Courses Catalog</h2>
    <table>
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Units</th>
                <th>Prerequisites</th>
                <th>Program</th>
                <th>Year Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CS101</td>
                <td>Introduction to Programming</td>
                <td>3</td>
                <td>None</td>
                <td>Computer Science</td>
                <td>1st Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>CS201</td>
                <td>Data Structures</td>
                <td>4</td>
                <td>CS101</td>
                <td>Computer Science</td>
                <td>2nd Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>CS301</td>
                <td>Algorithms</td>
                <td>4</td>
                <td>CS201</td>
                <td>Computer Science</td>
                <td>3rd Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>BA101</td>
                <td>Principles of Management</td>
                <td>3</td>
                <td>None</td>
                <td>Business Administration</td>
                <td>1st Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>BA201</td>
                <td>Financial Accounting</td>
                <td>3</td>
                <td>BA101</td>
                <td>Business Administration</td>
                <td>2nd Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>EE101</td>
                <td>Circuit Theory</td>
                <td>4</td>
                <td>None</td>
                <td>Electrical Engineering</td>
                <td>1st Year</td>
                <td>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>