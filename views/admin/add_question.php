<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Add Question</h3>
                <form action="/admin/save_question/<?php echo $contest['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="question">Question</label>
                        <textarea class="form-control" id="question" name="question" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="input">Input</label>
                        <textarea class="form-control" id="input" name="input" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="output">Output</label>
                        <textarea class="form-control" id="output" name="output" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="grade">Grade</label>
                        <input type="number" class="form-control" id="grade" name="grade" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>
        </div>
    </div>
</div>