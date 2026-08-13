<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\RoiCalculator\Http\Controllers;

use Crmleaf\Payroll\Calculators\RoiCalculator;
use Crmleaf\Payroll\Contracts\CalculationResult;
use Crmleaf\Payroll\Exceptions\InvalidInputException;
use Crmleaf\Payroll\Tools\RoiCalculator\Http\Requests\RoiCalculatorRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * The whole HTTP surface of Payroll ROI Calculator: one action, one calculator call.
 *
 * A GET with no input renders the form. Anything else validates, calculates and
 * answers in the caller's preferred format. Nothing here decides anything about
 * payroll - that all lives in Crmleaf\Payroll\Calculators\RoiCalculator - which is why the tool can
 * be embedded, mounted at any prefix, or ignored entirely in favour of calling
 * the calculator yourself.
 */
final class RoiCalculatorController
{
    public function __construct(
        private readonly RoiCalculator $calculator,
    ) {
    }

    public function __invoke(RoiCalculatorRequest $request): JsonResponse|View
    {
        if (!$request->submitted()) {
            return $this->view($request, null);
        }

        try {
            $result = $this->calculator->calculate(...$request->payload());
        } catch (InvalidInputException $e) {
            // A statutory *ineligibility* is never an exception - the calculator
            // returns a zero result and explains itself. Landing here means the
            // input was genuinely unusable, so 422 is the honest answer.
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'tool' => 'roi-calculator',
                    'message' => $e->getMessage(),
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->view($request, null, $e->getMessage());
        }

        if ($request->expectsJson()) {
            return new JsonResponse([
                'tool' => 'roi-calculator',
                'input' => $request->validated(),
                'data' => $result->toArray(),
                'explain' => $result->explain(),
                'working' => $result->steps(),
                'citations' => $result->citations(),
            ]);
        }

        return $this->view($request, $result);
    }

    private function view(RoiCalculatorRequest $request, ?CalculationResult $result, ?string $error = null): View
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = app('config');

        return view('roi-calculator::roi-calculator', [
            'result' => $result,
            'error' => $error,
            'input' => $request->submitted() ? $request->validated() : [],
            'defaults' => (array) $config->get('roi-calculator.defaults', []),
            'title' => (string) $config->get('roi-calculator.view.title', 'Payroll ROI Calculator'),
            'action' => $request->url(),
        ]);
    }
}
