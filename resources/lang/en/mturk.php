<?php
 return [
     'mturk'         => 'Mechanical Turk',
     'activity'      => 'Activity',
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
         'sent_to_rc' => 'Text Sent to RC'
     ],
     'action'        => [
         'sent_to_mturk'      => 'Contract successfully sent to Manual Transcription tasks',
         'sent_fail_to_mturk' => 'Contract could not send to Manual Transcription tasks',
         'approve'           => 'Assignment successfully approved.',
         'approve_fail'       => 'Assignment could not be approve.',
         'reject'           => 'Assignment successfully rejected.',
         'reject_fail'        => 'Assignment could not be reject.',
         'reset'              => 'HIT successfully reset.',
         'reset_fail'         => 'HIT could not be reset.',
         'sent_to_rc'         => 'Text successfully sent to RC.',
         'sent_fail_to_rc'    => 'Text could not be send to RC.',
         'reject_reason'      => 'Please enter reject reason for the assignment'
     ]
 ];
