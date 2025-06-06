<?php
$pageTitle = "Enroll New Student";
include 'header.php';
?>

<div class="enrollment-container" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
    <!-- Left Column - Student Information -->
    <div class="form-container" style="height: fit-content;">
        <form id="student-info-form">
            <h2 style="margin-bottom: 1.5rem;">Student Information</h2>

            <div class="form-group">
                <label for="student-id">Student ID</label>
                <input type="text" id="student-id" placeholder="Enter student ID" value="S2023-005"
                    readonly>
            </div>

            <div class="form-group">
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" placeholder="Enter full name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Enter email address">
            </div>

            <div class="form-group">
                <label for="program">Program</label>
                <select id="program">
                    <option value="">Select a program</option>
                    <option value="cs">Computer Science</option>
                    <option value="ba">Business Administration</option>
                    <option value="ee">Electrical Engineering</option>
                    <option value="psy">Psychology</option>
                    <option value="bio">Biology</option>
                </select>
            </div>

            <div class="form-group">
                <label for="year-level">Year Level</label>
                <select id="year-level">
                    <option value="">Select year level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
            </div>

            <div class="form-group">
                <label for="enrollment-date">Enrollment Date</label>
                <input type="date" id="enrollment-date">
            </div>
        </form>
    </div>

    <!-- Right Column - Course Enrollment -->
    <div class="form-container">
        <h2 style="margin-bottom: 1.5rem;">Course Enrollment</h2>
        <div id="course-selection-container">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <div id="units-counter" style="color: var(--text-light);">
                    Selected: 0 units (Max: 28 units)
                </div>
                <button type="button" id="add-course-btn" class="btn btn-secondary">
                    <i data-lucide="plus"></i> Add Course
                </button>
            </div>

            <div class="table-container"
                style="margin-bottom: 1.5rem; max-height: 500px; overflow-y: auto;">
                <table id="courses-table">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Units</th>
                            <th>Prerequisites</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                Please select a program and year level to view available courses
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="btn-group" style="justify-content: flex-end;">
            <button type="button" class="btn btn-secondary">Cancel</button>
            <button type="submit" form="student-info-form" class="btn">Enroll Student</button>
        </div>
    </div>
</div>