<?php
 return [
     'mturk'                    => 'Mechanical Turk',
     'activity'                 => 'Activity',
     'category'                 => 'Category',
     'created_on'               => 'Created on',
     'requiring_action'         => 'Requiring Action',
     'sent_to_rc_on'            => 'Sent to RC on',
     'index_title'              => 'Contracts Sent for Mturk',
     'contract_name'            => 'Contract Name',
     'pages'                    => 'Pages',
     'tasks'                    => 'Tasks',
     'completed'                => 'Completed',
     'approved'                 => 'Approved',
     'rejected'                 => 'Rejected',
     'action_name'              => 'Action',
     'not_found'                => 'Contract not found.',
     'contracts_sent_for_mturk' => 'Contracts Sent For Mturk',
     'total_tasks'              => 'Total Tasks',
     'tasks_completed'          => 'Tasks Completed',
     'tasks_approved'           => 'Task Approved',
     'tasks_requiring_action'   => 'Tasks requiring your action',
     'review_task'              => 'Please review the completed tasks before approval.',
     "review"                   => "Review",
     'all_task'                 => 'All Tasks',
     'task'                     => 'Task',
     'requiring_action'         => 'Requiring Action',
     'approved'                 => 'Approved',
     'rejected'                 => 'Rejected',
     'pending'                  => 'Pending',
     'search_hitid'             => 'Search By HIT Id',
     'contract_name'            => 'Contract Name',
     'hit_id'                   => 'HIT ID',
     'status'                   => 'Status',
     'task_not_found'           => 'Task not found.',
     'page_no'                  => 'Page No.',
     'hit'                      => 'HIT',
     'assignment_id'            => 'Assignment ID',
     'worker_id'                => 'Worker ID',
     'submit_time'              => 'Submit Time',
     'approve'                  => 'Approve',
     'reject'                   => 'Reject',
     'reset'                    => 'Reset',
     'approve_all'              => 'Approve All',
     'total_hit'                => 'Total HIT',
     'total_pages'              => 'Total Pages',
     'all_hit'                  => 'All Hit',
     'back'                     => 'Back',
     'on'                       => 'on',
     'send_to_rc'               => 'Send to RC',
     'rejection_reason'         => 'Write reason for rejection',
     'search'                   => 'Search',
     'all'                      => 'All',
     'mturk_approve'            => 'Are you sure you want to approve this assignment ?',
     'index_task'               => 'task',
     'view_on_amazon'           => 'View On Amazon',
     'sure_send_to_rc'          => 'Are you sure you want to send text to RC ?',
     'sent_to_rc'               => 'Sent to RC',
     'reset_hitid'              => "Reseting this HIT will delete this task and re-creates a new one for this page. Any worker who might be working but hasn\'t submitted the assignment for this task will be rejected. Select Ok to continue.",
     'mturk_rejection'          => "Rejection reason",
     'write_mturk_rejection'    => 'Write reason for rejection.',
     'balance_check'            => 'click here to check',
     'on'                       => 'on',
     'by'                       => 'by',
     'text_approve_all'         => 'Are you sure you want to approve all assignments ?',
     'reject-reason'            => 'Reason is required',
     'Completed'                => 'Completed',
     'Pending'                  => 'Pending',
     'Approved'                 => 'Approved',
     'Rejected'                 => 'Rejected',
     'hit_description'          => 'HIT Description',
     'write_hit_description'    => 'Write HIT Description',
     'qualification_id'         => 'Qualification Id',
     'write_qualification_id'   => '(Qualification ID is Optional). For using default Mechanical Turk qualifications, please leave this field blank',
     'reject_task'              => 'Reject task',
     'reset_task'              => 'Reset task',
     'text_explainer'          => ' <li><b>Note:</b></li>
                                    <li><b>M-Approved</b> (Manually approved) refers to RC/OLC site
                                        administrator-approved HITs.</li><li>
                                        <b>A-Approved</b> (Automatically approved) refers to HITs that are automatically
                                        approved by the MTurk system. HITs are automatically approved after 30 days
                                        of assignment submission. A-Approved HITs can be reset and sent to MTurk
                                        for retranscription.
                                    </li>',



     'log'           => [
         'create'     => ':contract sent to Mechanical Turk',
         'reject'     => 'HIT rejected',
         'approve'    => 'HIT approved',
         'reset'      => 'HIT reset',
         'sent_to_rc' => 'Text Sent to RC',
         'submitted'  => 'Assignment submitted'
     ],
     'action'        => [
         'sent_to_mturk'      => 'Contract successfully sent to Manual Transcription tasks',
         'sent_fail_to_mturk' => 'Contract could not send to Manual Transcription tasks',
         'approve'            => 'Assignment successfully approved.',
         'approve_fail'       => 'Assignment could not be approve.',
         'reject'             => 'Assignment successfully rejected.',
         'reject_fail'        => 'Assignment could not be reject.',
         'reset'              => 'HIT successfully reset.',
         'reset_fail'         => 'HIT could not be reset.',
         'sent_to_rc'         => 'Text successfully sent to RC.',
         'sent_fail_to_rc'    => 'Text could not be send to RC.',
         'reset_balance_low'  => 'Could not reset HIT deu to low balance in Mechnical Turk.',
         'reject_reason'      => 'Please enter reject reason for the assignment',
         'already_approved'   => 'HIT already approved on Mechanical Turk since no action was taken for 30 days after assignment submission.',
         'already_approved_and_reset'   => 'HIT already approved on Mechanical Turk since no action was taken for 30 days after assignment submission. :reset to reset HIT',
         'has_already_approved'   => 'HIT has already been approved ',
         'assignment_does_not_exists'   => 'Assignment does not exists',
         'hit_does_not_exists' => 'HIT does not exits',
         'hit_approved_cannot_be_rejected' => 'HIT has been approved already and cannot be rejected. Please approve it once again to update in database',
         'has_already_rejected'   => 'HIT has already been rejected',
         'hit_rejected_cannot_be_approved' => 'HIT has been rejected and cannot be approved. Please reject it once again to update in database',
         'hit_auto_reset'   => 'Previous HIT/assignment does not exist. Thus, new HIT is created',
         'reject_hit_auto_reset'   => 'Rejected prev HIT and created new one successfully',
     ]
 ];
