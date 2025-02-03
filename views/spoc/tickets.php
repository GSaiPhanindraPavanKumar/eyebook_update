<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
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
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Replies</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($activeTickets)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="p-3">
                                                <i class="ti-ticket text-muted mb-2" style="font-size: 2em;"></i>
                                                <p class="text-muted">No active tickets found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($activeTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                            <td><?php echo $ticket['reply_count']; ?></td>
                                            <td>
                                                <a href="/spoc/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                    View Ticket
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
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
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Closed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($closedTickets)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="p-3">
                                                <i class="ti-ticket text-muted mb-2" style="font-size: 2em;"></i>
                                                <p class="text-muted">No closed tickets found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($closedTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?></td>
                                            <td>
                                                <a href="/spoc/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                    View Ticket
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
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

<style>
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
    margin-bottom: -2px;
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom: 2px solid #007bff;
}

.table td {
    vertical-align: middle;
}
</style> 