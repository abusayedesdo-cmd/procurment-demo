<?php

namespace App\Services;

use App\Models\ProcurementCase;

/**
 * Builds the recurring "Procuring [Sub Category Name] for the [Category
 * Name]" phrasing used on the Committee Meeting Notice, Attendance and
 * Minutes/Rezulation documents, from a ProcurementCase + its linked PR.
 */
class CommitteeDocumentText
{
    public const VERB_BY_CATEGORY = [
        'Goods' => 'Procuring',
        'Services' => 'Hiring Consultant',
        'Works' => 'Hiring Contractor',
    ];

    public static function verb(ProcurementCase $case): string
    {
        return self::VERB_BY_CATEGORY[$case->category] ?? 'Procuring';
    }

    /** The broader chart-of-account category, e.g. "IT & Electronics". */
    public static function categoryName(ProcurementCase $case): string
    {
        return $case->purchaseRequisition?->category?->name ?: $case->category;
    }

    /** The specific subject of the PR — the Chart of Account name of its first line item. */
    public static function subCategoryName(ProcurementCase $case): string
    {
        $chartOfAccount = $case->purchaseRequisition?->items?->first()?->item?->chartOfAccount?->name;

        return $chartOfAccount ?: $case->title;
    }

    /** "Regarding the Procuring [Sub Category] for the [Category]." */
    public static function agendaLine(ProcurementCase $case): string
    {
        return self::verb($case) . ' ' . self::subCategoryName($case) . ' for the ' . self::categoryName($case);
    }

    public static function projectName(ProcurementCase $case): ?string
    {
        return $case->purchaseRequisition?->project_name;
    }

    public static function projectLocation(ProcurementCase $case): ?string
    {
        return $case->purchaseRequisition?->delivery_location;
    }

    public static function totalAmount(ProcurementCase $case): float
    {
        return (float) ($case->purchaseRequisition?->total_estimated_amount ?? $case->amount);
    }

    /** "Tender" / "RFQ" / "Sole Sourcing" / "Framework Agreement" label for the solicitation method. */
    public static function solicitationLabel(ProcurementCase $case): string
    {
        return $case->is_otm ? 'Tender' : $case->method;
    }
}