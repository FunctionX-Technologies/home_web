<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends ResourceController
{
    protected $format = 'json';

    // ✅ Export all projects to Excel
    public function exportProjectsExcel()
    {
        $projectModel = new ProjectModel();
        $projects = $projectModel->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Project Name');
        $sheet->setCellValue('C1', 'Status');
        $sheet->setCellValue('D1', 'Progress');
        $sheet->setCellValue('E1', 'Start Date');
        $sheet->setCellValue('F1', 'End Date');

        // Data
        $row = 2;
        foreach ($projects as $p) {
            $sheet->setCellValue('A' . $row, $p['id']);
            $sheet->setCellValue('B' . $row, $p['name']);
            $sheet->setCellValue('C' . $row, $p['status']);
            $sheet->setCellValue('D' . $row, $p['progress'] . '%');
            $sheet->setCellValue('E' . $row, $p['start_date']);
            $sheet->setCellValue('F' . $row, $p['end_date']);
            $row++;
        }

        // Download
        $writer = new Xlsx($spreadsheet);
        $filename = 'projects_report_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save("php://output");
        exit;
    }

    // ✅ Export tasks to PDF
    public function exportTasksPDF()
    {
        $taskModel = new TaskModel();
        $tasks = $taskModel
            ->select('tasks.*, users.name as developer, projects.name as project_name')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->findAll();

        $html = "<h2 style='text-align:center;'>Task Report</h2>
        <table border='1' cellpadding='8' cellspacing='0' width='100%'>
        <thead><tr>
        <th>ID</th><th>Project</th><th>Title</th><th>Status</th><th>Developer</th><th>Start</th><th>End</th>
        </tr></thead><tbody>";
        $html = '
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        background: #f9f9f9;
        color: #333;
    }
    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 20px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 12px;
    }
    th, td {
        border: 1px solid #999;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #2c3e50;
        color: #fff;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>

<h2>📊 Project Report</h2>
<table>
<thead>
    <tr>
        <th>ID</th>
        <th>Project Name</th>
        <th>Manager</th>
        <th>Status</th>
        <th>Progress</th>
        <th>Start Date</th>
        <th>End Date</th>
    </tr>
</thead>
<tbody>';


        foreach ($tasks as $t) {
            $html .= "<tr>
                <td>{$t['id']}</td>
                <td>{$t['project_name']}</td>
                <td>{$t['title']}</td>
                <td>{$t['status']}</td>
                <td>{$t['developer']}</td>
                <td>{$t['start_date']}</td>
                <td>{$t['end_date']}</td>
            </tr>";
        }

        $html .= "</tbody></table>";

        // Generate PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('tasks_report_' . date('Y-m-d') . '.pdf');
        exit;
    }
}
