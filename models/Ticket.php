<?php
namespace Models;

use PDO;

class Ticket {
    public static function generateTicketNumber() {
        return 'TKT-' . date('Y') . rand(100000, 999999);
    }

    public static function create($conn, $studentId, $universityId, $subject, $description) {
        $ticketNumber = self::generateTicketNumber();
        $stmt = $conn->prepare("INSERT INTO tickets (ticket_number, student_id, university_id, subject, description) 
                               VALUES (:ticket_number, :student_id, :university_id, :subject, :description)");
        return $stmt->execute([
            'ticket_number' => $ticketNumber,
            'student_id' => $studentId,
            'university_id' => $universityId,
            'subject' => $subject,
            'description' => $description
        ]);
    }

    public static function addReply($conn, $ticketId, $userId, $userRole, $message) {
        $stmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, user_role, message) 
                               VALUES (:ticket_id, :user_id, :user_role, :message)");
        return $stmt->execute([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'user_role' => $userRole,
            'message' => $message
        ]);
    }

    public static function closeTicket($conn, $ticketId, $closedById, $closedByRole) {
        $stmt = $conn->prepare("UPDATE tickets SET status = 'closed', closed_at = CURRENT_TIMESTAMP, 
                               closed_by_id = :closed_by_id, closed_by_role = :closed_by_role 
                               WHERE id = :ticket_id");
        return $stmt->execute([
            'ticket_id' => $ticketId,
            'closed_by_id' => $closedById,
            'closed_by_role' => $closedByRole
        ]);
    }

    public static function canClose($conn, $ticketId, $userId, $userRole) {
        if ($userRole === 'admin') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) FROM ticket_replies 
                WHERE ticket_id = :ticket_id 
                AND user_id = :user_id 
                AND user_role = :user_role";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'user_role' => $userRole
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    public static function getTicketsByStudent($conn, $studentId, $status) {
        $sql = "SELECT t.*, COUNT(tr.id) as reply_count 
                FROM tickets t 
                LEFT JOIN ticket_replies tr ON t.id = tr.ticket_id 
                WHERE t.student_id = :student_id AND t.status = :status 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'status' => $status
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTicketsByUniversity($conn, $universityId, $status = null) {
        $sql = "SELECT t.*, s.name as student_name, 
                (SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = t.id) as reply_count 
                FROM tickets t 
                JOIN students s ON t.student_id = s.id 
                WHERE t.university_id = :university_id";
        
        if ($status) {
            $sql .= " AND t.status = :status";
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        
        if ($status) {
            $stmt->execute([
                'university_id' => $universityId,
                'status' => $status
            ]);
        } else {
            $stmt->execute(['university_id' => $universityId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllTickets($conn) {
        $sql = "SELECT t.*, 
                       s.name as student_name,
                       u.name as university_name,
                       COUNT(tr.id) as reply_count 
                FROM tickets t 
                JOIN students s ON t.student_id = s.id 
                JOIN universities u ON t.university_id = u.id 
                LEFT JOIN ticket_replies tr ON t.id = tr.ticket_id 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTicketDetails($conn, $ticketId) {
        // Get ticket info
        $stmt = $conn->prepare("SELECT t.*, 
                                      s.name as student_name
                               FROM tickets t 
                               JOIN students s ON t.student_id = s.id 
                               WHERE t.id = :ticket_id");
        $stmt->execute(['ticket_id' => $ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            return null;
        }

        // Get replies with user names
        $stmt = $conn->prepare("SELECT tr.*, 
                                      CASE 
                                          WHEN tr.user_role = 'student' THEN s.name 
                                          WHEN tr.user_role = 'faculty' THEN f.name 
                                          WHEN tr.user_role = 'spoc' THEN sp.name 
                                          ELSE 'Admin' 
                                      END as user_name,
                                      tr.user_role,
                                      tr.created_at
                               FROM ticket_replies tr 
                               LEFT JOIN students s ON tr.user_id = s.id AND tr.user_role = 'student' 
                               LEFT JOIN faculty f ON tr.user_id = f.id AND tr.user_role = 'faculty' 
                               LEFT JOIN spocs sp ON tr.user_id = sp.id AND tr.user_role = 'spoc' 
                               WHERE tr.ticket_id = :ticket_id 
                               ORDER BY tr.created_at ASC");
        $stmt->execute(['ticket_id' => $ticketId]);
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['ticket' => $ticket, 'replies' => $replies];
    }

    public static function getCount($conn, $status = null) {
        $sql = "SELECT COUNT(*) FROM tickets";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        
        $stmt = $conn->prepare($sql);
        if ($status) {
            $stmt->execute(['status' => $status]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchColumn();
    }

    public static function getAverageResponseTime($conn) {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, t.created_at, tr.created_at)) as avg_time
                FROM tickets t
                JOIN ticket_replies tr ON t.id = tr.ticket_id
                WHERE tr.id IN (
                    SELECT MIN(id)
                    FROM ticket_replies
                    WHERE user_role != 'student'
                    GROUP BY ticket_id
                )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getAverageResolutionTime($conn) {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, closed_at)) as avg_time
                FROM tickets
                WHERE status = 'closed'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getCountByUniversity($conn) {
        $sql = "SELECT u.name as university_name, COUNT(*) as count
                FROM tickets t
                JOIN universities u ON t.university_id = u.id
                GROUP BY t.university_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCountByMonth($conn, $months = 6) {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       COUNT(*) as total,
                       SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM tickets
                WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['months' => $months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 