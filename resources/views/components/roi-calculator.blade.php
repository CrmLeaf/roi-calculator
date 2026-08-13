@props([
    'action' => null,
    'method' => 'post',
    'defaults' => [],
    'input' => [],
    'result' => null,
    'error' => null,
    'heading' => 'Payroll ROI Calculator',
    'tagline' => 'Quantify the return on moving off manual payroll.',
    'showWorking' => true,
])

<section class="crmleaf-tool crmleaf-tool--roi-calculator" data-crmleaf-tool="roi-calculator">
    <header class="crmleaf-tool__header">
        <h2 class="crmleaf-tool__heading">{{ $heading }}</h2>
        <p class="crmleaf-tool__tagline">{{ $tagline }}</p>
    </header>

    @if ($error)
        <p class="crmleaf-tool__error" role="alert">{{ $error }}</p>
    @endif

    <form class="crmleaf-tool__form"
          method="{{ strtolower($method) === 'get' ? 'get' : 'post' }}"
          action="{{ $action }}"
          data-crmleaf-form>
        @if (strtolower($method) !== 'get')
            @csrf
        @endif

        <label class="crmleaf-field">
            <span>Employees on payroll</span>
            <input type="number" step="1" inputmode="numeric" name="employee_count" value="{{ old('employee_count', $input['employee_count'] ?? ($defaults['employee_count'] ?? '')) }}" required>
        </label>

        <label class="crmleaf-field">
            <span>Loaded hourly cost of the payroll team</span>
            <input type="number" step="0.01" min="0" inputmode="decimal" name="hourly_rate" value="{{ old('hourly_rate', $input['hourly_rate'] ?? ($defaults['hourly_rate'] ?? '')) }}" required>
        </label>

        <label class="crmleaf-field">
            <span>Hours per payroll cycle today</span>
            <input type="number" step="any" inputmode="decimal" name="hours_per_cycle_manual" value="{{ old('hours_per_cycle_manual', $input['hours_per_cycle_manual'] ?? ($defaults['hours_per_cycle_manual'] ?? '')) }}" required>
        </label>

        <label class="crmleaf-field">
            <span>Payroll cycles per year</span>
            <input type="number" step="1" inputmode="numeric" name="cycles_per_year" value="{{ old('cycles_per_year', $input['cycles_per_year'] ?? ($defaults['cycles_per_year'] ?? '')) }}">
        </label>

        <label class="crmleaf-field">
            <span>Annual software cost</span>
            <input type="number" step="0.01" min="0" inputmode="decimal" name="software_annual_cost" value="{{ old('software_annual_cost', $input['software_annual_cost'] ?? ($defaults['software_annual_cost'] ?? '')) }}">
        </label>

        <label class="crmleaf-field">
            <span>Share of those hours automation removes</span>
            <input type="number" step="any" inputmode="decimal" name="automation_efficiency" value="{{ old('automation_efficiency', $input['automation_efficiency'] ?? ($defaults['automation_efficiency'] ?? '')) }}">
            <small>A fraction between 0 and 1. 0.8 means eight hours in ten disappear.</small>
        </label>

        <label class="crmleaf-field">
            <span>Late-filing penalties per year</span>
            <input type="number" step="0.01" min="0" inputmode="decimal" name="penalty_cost_per_year" value="{{ old('penalty_cost_per_year', $input['penalty_cost_per_year'] ?? ($defaults['penalty_cost_per_year'] ?? '')) }}">
        </label>

        <label class="crmleaf-field">
            <span>Cost of correcting payroll errors per year</span>
            <input type="number" step="0.01" min="0" inputmode="decimal" name="error_cost_per_year" value="{{ old('error_cost_per_year', $input['error_cost_per_year'] ?? ($defaults['error_cost_per_year'] ?? '')) }}">
        </label>

        <input type="hidden" name="tool" value="roi-calculator">

        <div class="crmleaf-tool__actions">
            <button type="submit" class="crmleaf-tool__submit">Calculate</button>
        </div>
    </form>

    {{-- The client-side path writes its answer here; the server-side path fills it below. --}}
    <div class="crmleaf-tool__output" data-crmleaf-output hidden></div>

    @if ($result)
        <div class="crmleaf-tool__result">
            <p class="crmleaf-tool__explain"><code>{{ $result->explain() }}</code></p>

            <table class="crmleaf-tool__figures">
                <tbody>
                @foreach ($result->toArray() as $key => $value)
                    @continue(is_array($value) || str_ends_with((string) $key, '_formatted'))
                    <tr>
                        <th scope="row">{{ ucfirst(str_replace('_', ' ', (string) $key)) }}</th>
                        <td>{{ $result->toArray()[$key.'_formatted'] ?? (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($showWorking && count($result->steps()))
                <details class="crmleaf-tool__working" open>
                    <summary>How this was worked out</summary>
                    <ol>
                        @foreach ($result->steps() as $step)
                            <li>
                                <span class="crmleaf-step__label">{{ $step->label }}</span>
                                @if ($step->amount)
                                    <span class="crmleaf-step__amount">{{ $step->amount->format() }}</span>
                                @endif
                                @if ($step->formula)
                                    <code class="crmleaf-step__formula">{{ $step->formula }}</code>
                                @endif
                                @if ($step->citation)
                                    <small class="crmleaf-step__citation">{{ $step->citation }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif

            @if (count($result->citations()))
                <ul class="crmleaf-tool__citations">
                    @foreach ($result->citations() as $citation)
                        <li>{{ $citation }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</section>
