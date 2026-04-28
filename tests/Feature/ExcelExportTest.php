<?php

namespace Tests\Feature;

use App\Models\EmailCargo;
use App\Models\EmailMovementType;
use App\Models\EmailRequest;
use App\Models\SystemRecord;
use App\Models\SystemStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Tests\TestCase;

class ExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_report_download_is_xlsx(): void
    {
        $admin = $this->seedCatalogsAndGetAdmin();
        $cargo = EmailCargo::query()->firstOrFail();
        $movementType = EmailMovementType::query()->where('slug', 'alta')->firstOrFail();

        EmailRequest::query()->create([
            'request_date' => '2026-04-20',
            'name' => 'Solicitud de correo',
            'email' => 'persona@example.com',
            'email_cargo_id' => $cargo->id,
            'email_movement_type_id' => $movementType->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('emails.report', ['format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());

        $spreadsheet = $this->loadSpreadsheetFromResponse($response->getContent());
        $this->assertSame('Reporte de correos', $spreadsheet->getActiveSheet()->getCell('A1')->getValue());
        $this->assertSame(Border::BORDER_THIN, $spreadsheet->getActiveSheet()->getStyle('A5')->getBorders()->getBottom()->getBorderStyle());
    }

    public function test_email_history_download_is_xlsx(): void
    {
        $admin = $this->seedCatalogsAndGetAdmin();
        $cargo = EmailCargo::query()->firstOrFail();
        $movementType = EmailMovementType::query()->where('slug', 'alta')->firstOrFail();

        $emailRequest = EmailRequest::query()->create([
            'request_date' => '2026-04-20',
            'name' => 'Historial correo',
            'email' => 'historial@example.com',
            'email_cargo_id' => $cargo->id,
            'email_movement_type_id' => $movementType->id,
            'created_by' => $admin->id,
        ]);

        $emailRequest->changeLogs()->create([
            'action' => 'updated',
            'content' => '<p>Solicitud actualizada por Administrador.</p><p>Campo correo actualizado.</p>',
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('emails.history.report', ['emailRequest' => $emailRequest, 'format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_system_report_download_is_xlsx(): void
    {
        $admin = $this->seedCatalogsAndGetAdmin();
        $status = SystemStatus::query()->where('slug', 'en-pruebas')->firstOrFail();

        SystemRecord::query()->create([
            'request_date' => '2026-04-18',
            'name' => 'Sistema operativo',
            'system_status_id' => $status->id,
            'pending_errors' => 1,
            'errors_in_progress' => 2,
            'in_review' => 3,
            'finalized' => 4,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('systems.report', ['format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_system_history_download_is_xlsx(): void
    {
        $admin = $this->seedCatalogsAndGetAdmin();
        $status = SystemStatus::query()->where('slug', 'en-pruebas')->firstOrFail();

        $system = SystemRecord::query()->create([
            'request_date' => '2026-04-18',
            'name' => 'Sistema historial',
            'system_status_id' => $status->id,
            'pending_errors' => 1,
            'errors_in_progress' => 0,
            'in_review' => 1,
            'finalized' => 0,
            'created_by' => $admin->id,
        ]);

        $system->changeLogs()->create([
            'action' => 'updated',
            'content' => '<div data-status-group="En pruebas internas"><p>Sistema actualizado por Administrador.</p><p>Se ajustó el estatus.</p></div>',
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('systems.history.report', ['system' => $system, 'format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());

        $spreadsheet = $this->loadSpreadsheetFromResponse($response->getContent());
        $detailColumnWidth = $spreadsheet->getActiveSheet()->getColumnDimension('F')->getWidth();
        $this->assertGreaterThanOrEqual(22, $detailColumnWidth);
        $this->assertLessThanOrEqual(42, $detailColumnWidth);
    }

    private function seedCatalogsAndGetAdmin(): User
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function loadSpreadsheetFromResponse(string $content): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx-test-');
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        @unlink($tempFile);

        return $spreadsheet;
    }
}