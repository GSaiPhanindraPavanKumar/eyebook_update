<?php
include 'sidebar.php'; 

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['email'];

$sql0 = "SELECT * FROM spocs WHERE email = :email";
$stmt0 = $conn->prepare($sql0);
$stmt0->execute([':email' => $username]);
$userData = $stmt0->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    die("User data not found.");
}

$university_id = $userData['university_id'];

function fetchFaculty($conn, $university_id, $search = '', $limit = 10, $offset = 0) {
    $sql = "SELECT * FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search) LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $likeSearch = "%$search%";
    $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
    $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countFaculty($conn, $university_id, $search = '') {
    $sql = "SELECT COUNT(*) as count FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search)";
    $stmt = $conn->prepare($sql);
    $likeSearch = "%$search%";
    $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
    $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$faculty = fetchFaculty($conn, $university_id, $search, $limit, $offset);
$totalFaculty = countFaculty($conn, $university_id, $search);
$totalPages = ceil($totalFaculty / $limit);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Faculty</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Faculty List</p><br>
                        <form method="get" action="manage_faculty.php">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-borderless">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Section</th>
                                        <th>Stream</th>
                                        <th>Year</th>
                                        <th>Department</th>
                                        <th>University ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($faculty)): ?>
                                        <?php foreach ($faculty as $member): ?>
                                            <tr>
                                                <td><?php echo $member['id']; ?></td>
                                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($member['section']); ?></td>
                                                <td><?php echo htmlspecialchars($member['stream']); ?></td>
                                                <td><?php echo htmlspecialchars($member['year']); ?></td>
                                                <td><?php echo htmlspecialchars($member['department']); ?></td>
                                                <td><?php echo $member['university_id']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9">No faculty members found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                        <a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>