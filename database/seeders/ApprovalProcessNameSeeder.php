<?php

namespace Database\Seeders;

use App\Models\ApprovalProcessName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApprovalProcessNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApprovalProcessName::query()->create([
            //'approval_process_name' => 'Leave Application',
            //'approval_process_name' => 'Strategic Plan Approval',
            //'approval_process_name' => 'RRF LAS Approval',
            //'approval_process_name' => 'Project Approval',
            //'approval_process_name' => 'M&E Plan Approval',
            //'approval_process_name' => 'Project RRF Approval',
            //'approval_process_name' => 'Project Workplan Approval',
            //'approval_process_name' => 'Purchase Request Approval',
            //'approval_process_name' => 'RFQ Approval',
            //'approval_process_name' => 'M&E WorkPlan Approval',
           // 'approval_process_name' => 'Tender Approval',
           //'approval_process_name' => 'Inventory Idle Approval', // database id 12
           //'approval_process_name' => 'Procurement Plan Approval', // database id 12
            //'approval_process_name' => 'HR Leave Application',// database id 15
           //'approval_process_name' => 'Employee Insurance Approval', // database id 14
           //'approval_process_name' => 'Vehicle Maintenance Approval',
           //'approval_process_name' => 'Vehicle Request Approval',
           //'approval_process_name' => 'Air Travel Approval',
           //'approval_process_name' => 'Book Request Approval',
           //'approval_process_name' => 'Complaint Request Approval',
           //'approval_process_name' => 'Research Matrix Approval',
           //'approval_process_name' => 'Research Plan Approval',
           //'approval_process_name' => 'Policy Approval',
           //'approval_process_name' => 'Advance Salary Approval',
           //'approval_process_name' => 'BOD Resolution Approval',
           //'approval_process_name' => 'BOD Meeting Approval',
           //'approval_process_name' => 'BOD Meeting Agenda Approval',
           //'approval_process_name' => 'Minutes of Meeting Approval',
           //'approval_process_name' => 'Item Dispose Approval',
           //'approval_process_name' => 'Employee Requisition Approval',
           //'approval_process_name' => 'Payroll approval',
           //'approval_process_name' => 'Appraisal approval',
            //'approval_process_name' => 'Employee Status Change approval',
            //'approval_process_name' => 'Book Reconciliation approval',
            //'approval_process_name' => 'Inventory Reconciliation approval',
            //'approval_process_name' => 'GRN Request Approval',
            //'approval_process_name' => 'Fuel Request approval',
            //'approval_process_name' => 'Project Budget Approval',
            //'approval_process_name' => 'Annual Budget Approval',
            //'approval_process_name' => 'COLA Approval',
            //'approval_process_name' => 'Voucher Approval',
            //'approval_process_name' => 'Audit Approval',
            //'approval_process_name' => 'Chart of Account Approval',
            //'approval_process_name' => 'Communication Event Approval',
            //'approval_process_name' => 'Retirement Benefit Approval',
            //'approval_process_name' => 'Timesheet Approval',
            //'approval_process_name' => 'Gate Pass Approval',
            //'approval_process_name' => 'Stock Request Approval',
            //'approval_process_name' => 'Employee Off Boarding Request Approval',
            //'approval_process_name' => 'Admin Bill Approval',
            //'approval_process_name' => 'Reimbursement Approval',
            //'approval_process_name' => 'Travel Expense Approval',
            //'approval_process_name' => 'Court Expense Approval',
            //'approval_process_name' => 'Manual Attendance Approval',
            //'approval_process_name' => 'Consultant Timesheet Approval',
            //'approval_process_name' => 'Gratuity Approval',
            //'approval_process_name' => 'Las Invoice Approval',
           //'approval_process_name' => 'Offer Letter Approval',
           //'approval_process_name' => 'Consultant Requisition Approval',
           //'approval_process_name' => 'Journal voucher Approval',
           //'approval_process_name' => 'Grant Close Out Approval',
           //'approval_process_name' => 'Sub Grant Close Out Approval',
           //'approval_process_name' => 'Grant Financial Report Approval',
           //'approval_process_name' => 'Sub Grant Financial Report Approval',
           //'approval_process_name' => 'Vendor Approval',
            //'approval_process_name' => 'Purchase Order Approval',
            //'approval_process_name' => 'Work Order Approval',
            //'approval_process_name' => 'Work Completion Approval',
            //'approval_process_name' => 'Consultant Contract Approval',
            //'approval_process_name' => 'Dispose Requests Approval',
            //'approval_process_name' => 'Email Template Requests Approval',
            //'approval_process_name' => 'Visit Reimbursement Requests Approval',
            //'approval_process_name' => 'Meeting Booking Requests Approval',
            //'approval_process_name' => 'Auction Requests Approval',
            //'approval_process_name' => 'Event Management Requests Approval',
            //'approval_process_name' => 'Salary Increments Requests Approval',
            //'approval_process_name' => 'Consultant Requisition Approval',
            //'approval_process_name' => 'Journal voucher Approval',
            //'approval_process_name' => 'Qualification Approval',
            //'approval_process_name' => 'Quotation Approval',

        ]);
    }
}
