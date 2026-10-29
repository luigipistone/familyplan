<?php

use App\Support\BackupManager;
use App\Support\ReminderPushSender;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', fn () => $this->comment(Inspiring::quote()));

Artisan::command('familyplan:send-reminders', function (ReminderPushSender $sender) {
    $sent = $sender->sendDueReminders();
    $this->info("Reminder push inviati: {$sent}");
})->purpose('Invia le notifiche push dei promemoria FamilyPlan in scadenza');

Artisan::command('familyplan:run-scheduled-backups', function (BackupManager $backupManager) {
    $created = $backupManager->runScheduledBackup();
    $this->info($created ? 'Backup automatico creato.' : 'Nessun backup dovuto.');
})->purpose('Esegue i backup automatici FamilyPlan quando dovuti');

Artisan::command('familyplan:prune-automatic-backups', function (BackupManager $backupManager) {
    $deleted = $backupManager->pruneAutomaticBackups();
    $this->info("Backup automatici precedenti eliminati: {$deleted}. Conservati gli ultimi 5.");
})->purpose('Conserva gli ultimi cinque backup automatici senza eliminare quelli manuali');
