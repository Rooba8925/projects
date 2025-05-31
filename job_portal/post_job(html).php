<form action="post_job.php" method="POST">
    <input type="text" name="title" placeholder="Job Title" required>
    <textarea name="description" placeholder="Job Description" required></textarea>
    <input type="text" name="location" placeholder="Job Location" required>
    <input type="number" name="salary" placeholder="Salary" required>
    
    <select name="category" required>
        <option value="">Select Category</option>
        <option value="IT">IT</option>
        <option value="Finance">Finance</option>
        <option value="Healthcare">Healthcare</option>
        <option value="Education">Education</option>
    </select>

    <button type="submit">Post Job</button>
</form>
