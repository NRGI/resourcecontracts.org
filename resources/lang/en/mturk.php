<?php
 return [
     'mturk'         => 'Mechanical Turk',
     'activity'      => 'Activity',
     'category'      => 'Category',
     'created_on' => 'Created on',
     'requiring_action' => 'Requiring Action',
     'sent_to_rc_on' => 'Sent to RC on',
     'index_title'   => 'Contracts Sent for Mturk',
     'contract_name' => 'Contract Name',
     'pages'         => 'Pages',
     'tasks'         => 'Tasks',
     'completed'     => 'Completed',
     'approved'      => 'Approved',
     'rejected'      => 'Rejected',
     'action_name'   => 'Action',
     'not_found'     => 'Contract not found.',
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
         'reject_reason'      => 'Please enter reject reason for the assignment',
         'already_approved'   => 'HIT already approved on Mechanical Turk since no action was taken for 30 days after assignment submission.',
         'already_approved_and_reset'   => 'HIT already approved on Mechanical Turk since no action was taken for 30 days after assignment submission. :reset to reset HIT'
     ]
 ];
