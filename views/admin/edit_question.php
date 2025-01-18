<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Edit Question</h3>
                <form action="/admin/update_question/<?php echo $question['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="question">Question</label>
                        <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($question['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="input">Input</label>
                        <textarea class="form-control" id="input" name="input" rows="3" required><?php echo htmlspecialchars($question['input']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="output">Output</label>
                        <textarea class="form-control" id="output" name="output" rows="3" required><?php echo htmlspecialchars($question['output']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="grade">Grade</label>
                        <input type="number" class="form-control" id="grade" name="grade" value="<?php echo htmlspecialchars($question['grade']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </form>
            </div>
        </div>
    </div>
</div>