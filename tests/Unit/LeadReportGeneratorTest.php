<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../packages/automas/SmartReports/src/Services/ReportGenerators/LeadReportGenerator.php';

use Automas\SmartReports\Services\ReportGenerators\LeadReportGenerator;

class LeadReportGeneratorTest extends TestCase
{
    public function test_it_normalizes_lead_status_values(): void
    {
        $this->assertSame('Converted', LeadReportGenerator::normalizeLeadStatus(['is_converted' => 1, 'is_active' => 1]));
        $this->assertSame('Active', LeadReportGenerator::normalizeLeadStatus(['is_converted' => 0, 'is_active' => 1]));
        $this->assertSame('Inactive', LeadReportGenerator::normalizeLeadStatus(['is_converted' => 0, 'is_active' => 0]));
    }

    public function test_it_normalizes_lead_status_from_model_like_objects(): void
    {
        $row = new class {
            public $is_converted = 0;
            public $is_active = 1;
        };

        $this->assertSame('Active', LeadReportGenerator::normalizeLeadStatus($row));
    }
}
