<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\RoiCalculator\Http\Requests;

use Crmleaf\Payroll\Money;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the wire input for Payroll ROI Calculator and turns it into named arguments
 * for Crmleaf\Payroll\Calculators\RoiCalculator::calculate().
 *
 * Optional fields that were not sent are left out of the payload entirely
 * rather than passed as null, so the calculator's own documented defaults apply
 * and there is exactly one place each default is written down.
 */
final class RoiCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        if (!$this->submitted()) {
            return [];
        }

        return [
            'employee_count' => ['required', 'integer', 'min:1', 'max:1000000'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'hours_per_cycle_manual' => ['required', 'numeric', 'min:0', 'max:2000'],
            'cycles_per_year' => ['nullable', 'integer', 'min:1', 'max:53'],
            'software_annual_cost' => ['nullable', 'numeric', 'min:0'],
            'automation_efficiency' => ['nullable', 'numeric', 'between:0,1'],
            'penalty_cost_per_year' => ['nullable', 'numeric', 'min:0'],
            'error_cost_per_year' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Named arguments for RoiCalculator::calculate().
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $input */
        $input = $this->validated();

        $payload = [
            'employeeCount' => (int) $input['employee_count'],
            'hourlyRate' => Money::fromRupees((float) $input['hourly_rate']),
            'hoursPerCycleManual' => (float) $input['hours_per_cycle_manual'],
        ];

        if (array_key_exists('cycles_per_year', $input) && $input['cycles_per_year'] !== null) {
            $payload['cyclesPerYear'] = (int) $input['cycles_per_year'];
        }

        if (array_key_exists('software_annual_cost', $input) && $input['software_annual_cost'] !== null) {
            $payload['softwareAnnualCost'] = Money::fromRupees((float) $input['software_annual_cost']);
        }

        if (array_key_exists('automation_efficiency', $input) && $input['automation_efficiency'] !== null) {
            $payload['automationEfficiency'] = (float) $input['automation_efficiency'];
        }

        if (array_key_exists('penalty_cost_per_year', $input) && $input['penalty_cost_per_year'] !== null) {
            $payload['penaltyCostPerYear'] = Money::fromRupees((float) $input['penalty_cost_per_year']);
        }

        if (array_key_exists('error_cost_per_year', $input) && $input['error_cost_per_year'] !== null) {
            $payload['errorCostPerYear'] = Money::fromRupees((float) $input['error_cost_per_year']);
        }

        return $payload;
    }

    /**
     * A bare GET renders an empty form; everything else is a submission.
     */
    public function submitted(): bool
    {
        return $this->isMethod('post') || $this->expectsJson() || $this->query->count() > 0;
    }
}
