<?php
$pageTitle = "Student Management";
include 'header.php';
?>

<div class="search-filter">
    <div class="search-box">
        <input type="text" placeholder="Search students...">
    </div>
    <button class="btn btn-secondary">
        <i data-lucide="filter"></i> Filter
    </button>
    <button class="btn">
        <i data-lucide="plus"></i> Add Student
    </button>
</div>

<div class="table-container" style="overflow-x: auto;">
    <table style="width: 100%;">
        <colgroup>
            <col style="width: 100px;"> <!-- Student ID -->
            <col style="width: 150px;"> <!-- Last Name -->
            <col style="width: 150px;"> <!-- First Name -->
            <col style="width: 150px;"> <!-- Middle Name -->
            <col style="width: 80px;"> <!-- Suffix -->
            <col style="width: 70px;"> <!-- Age -->
            <col style="width: 120px;"> <!-- Date of Birth -->
            <col style="width: 80px;"> <!-- Sex -->
            <col style="width: 120px;"> <!-- Nationality -->
            <col style="width: 150px;"> <!-- Mobile Number -->
            <col style="width: 200px;"> <!-- Email -->
            <col style="width: 120px;"> <!-- Date Created -->
            <col style="width: 180px;"> <!-- Program Name -->
            <col style="width: 150px;"> <!-- Actions -->
        </colgroup>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Suffix</th>
                <th>Age</th>
                <th>Date of Birth</th>
                <th>Sex</th>
                <th>Nationality</th>
                <th>Mobile Number</th>
                <th>Email</th>
                <th>Date Created</th>
                <th>Program Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>S2023-001</td>
                <td>Johnson</td>
                <td>Alex</td>
                <td>Michael</td>
                <td>Jr.</td>
                <td>21</td>
                <td>2002-05-15</td>
                <td>Male</td>
                <td>American</td>
                <td>+1 555-123-4567</td>
                <td>alex.j@university.edu</td>
                <td>2023-01-10</td>
                <td>Computer Science</td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>S2023-002</td>
                <td>Garcia</td>
                <td>Maria</td>
                <td>Isabel</td>
                <td></td>
                <td>20</td>
                <td>2003-07-22</td>
                <td>Female</td>
                <td>Spanish</td>
                <td>+34 612-345-678</td>
                <td>maria.g@university.edu</td>
                <td>2023-01-12</td>
                <td>Business Administration</td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>S2023-003</td>
                <td>Wilson</td>
                <td>James</td>
                <td>Robert</td>
                <td>III</td>
                <td>19</td>
                <td>2004-03-08</td>
                <td>Male</td>
                <td>British</td>
                <td>+44 7123-45678</td>
                <td>james.w@university.edu</td>
                <td>2023-01-15</td>
                <td>Electrical Engineering</td>
                <td>
                    <button class="action-btn"><i data-lucide="eye"></i></button>
                    <button class="action-btn"><i data-lucide="edit"></i></button>
                    <button class="action-btn"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <tr>
                <td>S2023-004</td>
                <td>Chen</td>
                <td>Sarah</td>
                <td>Li</td>
                <td></td>
                <td>22</td>
                <td>2001-11-30</td>
                <td>Female</td>
                <td>Chinese</td>
                <td>+86 138-1234-5678</td>
                <td>sarah.c@university.edu</td>
                <td>2023-01-18</td>
                <td>Psychology</td>
                <td>
                    <div class="student-actions">
                        <button class="action-btn" title="View"><i data-lucide="eye"></i></button>
                        <button class="action-btn" title="Edit"><i data-lucide="edit"></i></button>
                        <button class="action-btn" title="Delete"><i data-lucide="trash-2"></i></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
    <div style="color: var(--text-light); font-size: 0.9rem;">
        Showing 1 to 4 of 24 students
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button class="btn btn-secondary" disabled>
            <i data-lucide="chevron-left"></i> Previous
        </button>
        <button class="btn btn-secondary">
            Next <i data-lucide="chevron-right"></i>
        </button>
    </div>
</div>