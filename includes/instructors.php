<?php
$pageTitle = "Instructor Management";
include 'header.php';
?>


<div class="search-filter">
    <div class="search-box">
        <input type="text" placeholder="Search instructors...">
    </div>
    <button class="btn btn-secondary">
        <i data-lucide="filter"></i> Filter
    </button>
    <button class="btn">
        <i data-lucide="plus"></i> Add Instructor
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Instructor ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Courses</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>I2023-001</td>
                <td>Dr. Robert Smith</td>
                <td>robert.s@university.edu</td>
                <td>Computer Science</td>
                <td>3</td>
                <td><span class="status active">Active</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
            <tr>
                <td>I2023-002</td>
                <td>Prof. Emily Davis</td>
                <td>emily.d@university.edu</td>
                <td>Business</td>
                <td>2</td>
                <td><span class="status active">Active</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
            <tr>
                <td>I2023-003</td>
                <td>Dr. Michael Brown</td>
                <td>michael.b@university.edu</td>
                <td>Engineering</td>
                <td>4</td>
                <td><span class="status inactive">On Leave</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
            <tr>
                <td>I2023-004</td>
                <td>Prof. Lisa Wong</td>
                <td>lisa.w@university.edu</td>
                <td>Psychology</td>
                <td>3</td>
                <td><span class="status active">Active</span></td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>