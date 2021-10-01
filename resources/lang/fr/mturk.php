<?php
 return [
     'mturk'                       => "Turc Mécanique",
     'activity'                    => "Activité",
     'category'                    => "Catégorie",
     'created_on'                  => "Créé le",
     'requiring_action'            => "Action requise",
     'sent_to_rc_on'               => "Envoyé au RC le",
     'index_title'                 => "Contrats envoyés à Mturk",
     'contract_name'               => "Nom du contrat",
     'pages'                       => "Pages",
     'tasks'                       => "Tâches",
     'completed'                   => "Accompli",
     'approved'                    => "Approuvé",
     'rejected'                    => "Rejeté",
     'action_name'                 => "Action",
     'not_found'                   => "Contrat non trouvé",
     'contracts_sent_for_mturk'    => "Contrats envoyés à Mturk",
     'total_tasks'                 => "Nombre total de tâches",
     'tasks_completed'             => "Tâches accomplies",
     'tasks_approved'              => "Tâche approuvée",
     'tasks_requiring_action'      => "Tâches requérant une action de votre part",
     'review_task'                 => "Veuillez revoir les tâches accomplies avant leur approbation ",
     "review"                      => "la revue",
     'all_task'                    => "Toutes les tâches",
     'task'                        => "Tâches",
     'requiring_action'            => "Action requise",
     'approved'                    => "Approuvé",
     'rejected'                    => "Rejeté",
     'pending'                     => "En attente",
     'search_hitid'                => "Rechercher selon l'identificateur HIT",
     'contract_name'               => "Nom du contrat",
     'hit_id'                      => "Identificateur HIT ",
     'status'                      => "État",
     'task_not_found'              => "Tâche non trouvée",
     'page_no'                     => "Page numéro",
     'hit'                         => "HIT (Tâches pour intelligence humaine)",
     'assignment_id'               => "Identificateur de mission ",
     'worker_id'                   => "Identificateur d'employé",
     'submit_time'                 => "Heure d'envoi",
     'approve'                     => "Approuver",
     'reject'                      => "Rejeter",
     'reset'                       => "Réinitialiser",
     'approve_all'                 => "Tout approuver",
     'total_hit'                   => "Nombre total de HIT",
     'total_pages'                 => "Nombre total de pages",
     'all_hit'                     => "Toutes les HIT",
     'back'                        => "Retour",
     'on'                          => "Activation",
     'send_to_rc'                  => "Envoyer à RC",
     'rejection_reason'            => "Mentionner la raison du rejet",
     'search'                      => "Rechercher",
     'all'                         => "Tout",
     'mturk_approve'               => "Êtes-vous sûr de vouloir approuver cette mission ?",
     'index_task'                  => "tâche",
     'view_on_amazon'              => "Voir sur Amazon",
     'sure_send_to_rc'             => "Êtes-vous sûr de vouloir envoyer du texte au RC ?",
     'sent_to_rc'                  => "Envoyé à RC",
     'reset_hitid'                 => "Reseting cette HIT supprimera cette tâche et re-crée un nouveau pour cette page. Tout travailleur qui pourrait travailler, mais n'a pas présenté l'affectation de cette tâche sera rejetée. Sélectionnez Ok pour continuer.",
     'mturk_rejection'             =>   "Motif de rejet",  
     'write_mturk_rejection'       => "Ecrire raison du rejet.",
     'balance_check'               => "cliquez ici pour vérifier",
     'on'                          => "sur",
     'by'                          => "par",
     'text_approve_all'            => "Etes-vous sûr de vouloir approuver toutes les affectations ?",
     'reject-reason'               => "Raison est nécessaire",
     'Completed'                   => "Accompli",
     'Pending'                     => "En attente",
     'Approved'                    => "Approuvé",
     'Rejected'                    => "Rejeté",
     'hit_description'             => "HIT description",
     'writ_hit_description'        => "Rédiger la description du HIT", 
     'reject_task'                => "Rejeter la tâche",
     'reset_task'                  => 'Réinitialiser la tâche',



     'log'           =>[
         'create'     => ":Contrat envoyé au Turc Mécanique",
         'reject'     => "HIT rejetée",
         'approve'    => "HIT approuvée",
         'reset'      => "HIT réinitialisée",
         'sent_to_rc' => "Texte envoyé au RC",
         'submitted'  => "Mission envoyée"
     ],
     'action'        => [
         'sent_to_mturk'      => "Envoi réussi du contrat aux tâches de transcription manuelle",
         'sent_fail_to_mturk' => "Ne pas envoyer le contrat aux tâches de transcription manuelle",
         'approve'            => "Mission approuvée avec succès",
         'approve_fail'       => "Impossible d'approuver la mission",
         'reject'             => "Rejet réussi de la mission",
         'reject_fail'        => "Impossible de rejeter la mission",
         'reset'              => "Réinitialisation réussie des HIT",
         'reset_fail'         => "Impossible de réinitialiser les HIT",
         'sent_to_rc'         => "Envoi réussi du texte au RC",
         'sent_fail_to_rc'    => "Impossible d'envoyer le texte au RC",
         'reject_reason'      => "Veuillez mentionner la raison du rejet de la mission",
         'already_approved'   => "HIT déjà approuvée sur le Turc Mécanique vu qu'aucune action n'a été entreprise après 30 jours de l'envoi de la mission.",
         'already_approved_and_reset'   => "HIT déjà approuvée sur le Turc Mécanique vu qu'aucune action n'a été entreprise après 30 jours de l'envoi de la mission: Réinitialisez pour la réinitialisation des HIT.",
         'has_already_approved'   => 'HIT a déjà été approuvé',
         'assignment_does_not_exists'   => "L'affectation n'existe pas",
         'hit_does_not_exists' => 'Hit ne sort pas',
         'hit_approved_cannot_be_rejected' => "Le hit a déjà été approuvé et ne peut pas être rejeté. Veuillez l'approuver à nouveau pour mettre à jour dans la base de données",
         'has_already_rejected'   => 'HIT a déjà été rejeté',
         'hit_rejected_cannot_be_approved' => "Le hit a été rejeté et ne peut pas être approuvé. Veuillez le rejeter à nouveau pour mettre à jour dans la base de données",
         'hit_auto_reset'  =>  "Le hit/l'affectation précédente n'existe pas. Ainsi, un nouveau hit est créé",
         'reject_hit_auto_reset'=> "Hit précédent rejeté et créé un nouveau avec succès"

     ]
 ];
