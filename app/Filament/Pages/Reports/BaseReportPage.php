<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use App\Enums\ReportDateRange;
use App\Enums\ReportExportFormat;
use App\Filament\Pages\Reports\Concerns\AuthorizesReports;
use App\Modules\Reports\Services\ReportDateRangeFactory;
use App\Modules\Reports\Services\ReportExportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseReportPage extends Page
{
    use AuthorizesReports;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.reports.report';

    public string $dateRange = 'this_month';

    public ?string $startDate = null;

    public ?string $endDate = null;

    abstract protected function reportKey(): string;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->form([
                    Select::make('dateRange')
                        ->label('Date Range')
                        ->options(ReportDateRange::options())
                        ->required()
                        ->default($this->dateRange),
                    DatePicker::make('startDate')
                        ->label('Custom Start'),
                    DatePicker::make('endDate')
                        ->label('Custom End'),
                ])
                ->action(function (array $data): void {
                    $this->dateRange = $data['dateRange'];
                    $this->startDate = $data['startDate'] ?? null;
                    $this->endDate = $data['endDate'] ?? null;
                }),
            Action::make('csv')
                ->label('CSV')
                ->action(fn (): StreamedResponse => $this->export(ReportExportFormat::CSV)),
            Action::make('excel')
                ->label('Excel')
                ->action(fn (): StreamedResponse => $this->export(ReportExportFormat::EXCEL)),
            Action::make('pdf')
                ->label('PDF')
                ->action(fn (): StreamedResponse => $this->export(ReportExportFormat::PDF)),
        ];
    }

    protected function export(ReportExportFormat $format): StreamedResponse
    {
        return app(ReportExportService::class)->export(
            $this->reportKey(),
            $format,
            auth()->user(),
            app(ReportDateRangeFactory::class)->make(
                ReportDateRange::from($this->dateRange),
                $this->startDate,
                $this->endDate,
            ),
        );
    }
}
