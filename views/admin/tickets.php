<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Support Tickets</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="ticketTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab">
                    Active Tickets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                    Closed Tickets
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="ticketTabContent">
            <!-- Active Tickets -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="activeTicketsTable">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>University</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Last Updated</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($activeTickets)): ?>
                                        <?php foreach ($activeTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['ticket_number'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['student_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['university_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject'] ?? ''); ?></td>
                                            <td><?php echo !empty($ticket['created_at']) ? date('M d, Y H:i', strtotime($ticket['created_at'])) : ''; ?></td>
                                            <td><?php echo !empty($ticket['updated_at']) ? date('M d, Y H:i', strtotime($ticket['updated_at'])) : ''; ?></td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td>
                                                <?php if (!empty($ticket['id'])): ?>
                                                <a href="/admin/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                    View
                                                </a>
                                                <button class="btn btn-warning btn-sm" onclick="closeTicket(<?php echo $ticket['id']; ?>)">
                                                    Close
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No active tickets found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closed Tickets -->
            <div class="tab-pane fade" id="closed" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="closedTicketsTable">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>University</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Closed</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($closedTickets)): ?>
                                        <?php foreach ($closedTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['ticket_number'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['student_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['university_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject'] ?? ''); ?></td>
                                            <td><?php echo !empty($ticket['created_at']) ? date('M d, Y H:i', strtotime($ticket['created_at'])) : ''; ?></td>
                                            <td><?php echo !empty($ticket['closed_at']) ? date('M d, Y H:i', strtotime($ticket['closed_at'])) : ''; ?></td>
                                            <td>
                                                <span class="badge badge-secondary">Closed</span>
                                            </td>
                                            <td>
                                                <?php if (!empty($ticket['id'])): ?>
                                                <a href="/admin/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                    View
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No closed tickets found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

<script>
$(document).ready(function() {
    // Initialize DataTables with advanced features
    const commonConfig = {
        "pageLength": 10,
        "searching": true,
        "ordering": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "language": {
            "search": "Search tickets:",
            "lengthMenu": "Show _MENU_ tickets per page"
        },
        "responsive": true,
        "columns": [
            null, // Ticket #
            null, // Student
            null, // University
            null, // Subject
            null, // Created/Closed
            null, // Last Updated/Closed
            { "orderable": false }, // Status
            { "orderable": false }  // Actions
        ]
    };

    $('#activeTicketsTable').DataTable({
        ...commonConfig,
        "order": [[6, "desc"]], // Sort by last updated
    });
    
    $('#closedTicketsTable').DataTable({
        ...commonConfig,
        "order": [[6, "desc"]], // Sort by closed date
    });
});

function closeTicket(ticketId) {
    if (confirm('Are you sure you want to close this ticket?')) {
        $.ajax({
            url: '/admin/close_ticket',
            method: 'POST',
            data: { ticket_id: ticketId },
            success: function(response) {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error closing ticket');
                }
            },
            error: function() {
                alert('Error closing ticket');
            }
        });
    }
}
</script>

<style>
.dataTables_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 15px;
}

.dataTables_wrapper .dataTables_length {
    float: left;
    margin-bottom: 15px;
}

.badge {
    padding: 5px 10px;
    font-size: 12px;
}

.table td {
    vertical-align: middle;
}
</style> 