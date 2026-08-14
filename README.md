# Payroll ROI Calculator

Quantify the return on moving off manual payroll.

Turns "payroll takes us two days a month" into a number a finance director can sign off, with the assumptions visible rather than buried in a vendor slide.

One of the [CRMLeaf payroll tools](https://github.com/crmleaf). The arithmetic
and the dated statutory rate tables live in
[`crmleaf/payroll-core`](https://github.com/crmleaf/payroll-core); this package is
the thin skin that makes one calculator installable, mountable and embeddable on
its own.

> [!TIP]
> **Nothing to install to try it.** [Payroll ROI Calculator on INDPayroll](https://www.indpayroll.com/free-tools/roi-calculator?utm_source=github&utm_medium=referral&utm_campaign=payroll-tools)
> is this calculator, hosted and free, and
> [all fifteen tools](https://www.indpayroll.com/free-tools?utm_source=github&utm_medium=referral&utm_campaign=payroll-tools) are there. Install the package when you want
> it inside your own application.

> [!NOTE]
> A wrong figure or an out-of-date rate is almost always a
> [`payroll-core`](https://github.com/crmleaf/payroll-core/issues) matter, since
> that is where the tables live. Anything about this tool's routes, views or
> browser asset belongs here.

## Install

**Composer** - Laravel auto-discovers the service provider, so this is the whole
setup:

```bash
composer require crmleaf/roi-calculator
```

> [!NOTE]
> Not on Packagist yet. Until it is, point Composer at the two repositories in
> **your own project's** `composer.json` and the same `require` works, because
> Composer reads the tags:
>
> ```json
> "repositories": [
>     { "type": "vcs", "url": "https://github.com/crmleaf/roi-calculator.git" },
>     { "type": "vcs", "url": "https://github.com/crmleaf/payroll-core.git" }
> ]
> ```
>
> Both entries are needed, and they have to be in the root project: Composer
> ignores a `repositories` block inside an installed dependency, so listing only
> this package will not resolve `crmleaf/payroll-core`.

**npm** - the same calculation, re-exported from `@crmleaf/payroll-js` so you can
install this one tool and nothing else:

```bash
npm install @crmleaf/roi-calculator
```

> [!NOTE]
> Not on npm yet either. The script-tag route below needs no registry and works
> today. Installing this package straight from git will not resolve
> `@crmleaf/payroll-js`, for the same reason as above.

**A plain script tag** - no build step, no bundler, no server. Build the browser
bundle once and serve the file yourself:

```html
<script src="/js/payroll.min.js"></script>
<script>
const result = CrmleafPayroll.roi({
  employeeCount: 250,
  hourlyRate: 900,
  hoursPerCycleManual: 24,
  softwareAnnualCost: 180000,
});
console.log(result.explain);
</script>
```

`payroll.min.js` is the single-file browser build. Get it by running
`npm run build` in [`@crmleaf/payroll-js`][js] and copying `dist/payroll.min.js`
into whatever your site serves as static assets.

> A hosted CDN build is coming soon, which will reduce this to a single URL.
> Serving the file yourself works today and keeps working afterwards - it is the
> only option that needs no third-party request, so plenty of projects will want
> to stay on it.

### See it working first

`demo/index.html` in this repository is a working copy of Payroll ROI Calculator in one file:
the form, the calculation and the working, with no build step and no server. Drop
`payroll.min.js` beside it and open it from disk.

```bash
cp /path/to/payroll-js/dist/payroll.min.js demo/
open demo/index.html
```

Nothing on that page reaches the network, which is the point: it is a calculator
people paste salary figures into.

## Use it

**Plain PHP**, no framework and no container:

```php
use Crmleaf\Payroll\Calculators\RoiCalculator;
use Crmleaf\Payroll\Money;

$result = (new RoiCalculator())->calculate(
    employeeCount: 250,
    hourlyRate: Money::fromRupees(900),
    hoursPerCycleManual: 24,
    softwareAnnualCost: Money::fromRupees(180_000),
);

echo $result->explain();      // the formula with the real operands in it
echo $result->workings();     // every step, one per line, with its citation
print_r($result->toArray());  // snake_case, ready for JSON
```

**Laravel** - resolve it from the container, or type-hint it anywhere:

```php
use Crmleaf\Payroll\Calculators\RoiCalculator;

public function show(RoiCalculator $calculator)
{
    return $calculator->calculate(
        employeeCount: 250,
        hourlyRate: Money::fromRupees(900),
        hoursPerCycleManual: 24,
        softwareAnnualCost: Money::fromRupees(180_000),
    )->toArray();
}
```

**Blade** - one component, no controller:

```blade
<x-crmleaf::roi-calculator />
```

**HTTP** - off by default. Publish the config and turn the route on:

```bash
php artisan vendor:publish --tag=roi-calculator-config
```

```php
// config/roi-calculator.php
'route' => ['enabled' => true, 'prefix' => 'tools'],
```

```bash
curl -X POST https://example.test/tools/roi-calculator \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"employee_count":250,"hourly_rate":900,"hours_per_cycle_manual":24,"software_annual_cost":180000}'
```

The JSON response carries the figures, the working and the statutory citations:

```json
{
  "tool": "roi-calculator",
  "data": { "…": "every figure, snake_case, with a *_formatted twin" },
  "explain": "the formula with the real operands substituted",
  "working": [{ "label": "…", "amount": 0, "formula": "…", "citation": "…" }],
  "citations": ["…"]
}
```

**JavaScript**:

```js
import { roi } from '@crmleaf/roi-calculator';

const result = roi({
  employeeCount: 250,
  hourlyRate: 900,
  hoursPerCycleManual: 24,
  softwareAnnualCost: 180000,
});
```

## No server needed

The maths here is arithmetic over versioned rate tables, so it runs anywhere.
The published asset binds the markup and computes in the browser:

```bash
php artisan vendor:publish --tag=roi-calculator-assets
```

```html
<section data-crmleaf-tool="roi-calculator">
  <form data-crmleaf-form> … </form>
  <div data-crmleaf-output hidden></div>
</section>

<script src="/js/payroll.min.js"></script>
<script src="/vendor/roi-calculator/roi-calculator.js"></script>
```

If the browser build is absent the script does nothing and the form posts to the
server instead, so the page works either way.

## Inputs

| Field | Type | Required | Default | Notes |
|-------|------|----------|---------|-------|
| `employee_count` | integer | Yes | `250` |  |
| `hourly_rate` | money (₹) | Yes | `900` |  |
| `hours_per_cycle_manual` | number | Yes | `24` |  |
| `cycles_per_year` | integer | No | `12` |  |
| `software_annual_cost` | money (₹) | No | `180000` |  |
| `automation_efficiency` | number | No | `0.8` | A fraction between 0 and 1. 0.8 means eight hours in ten disappear. |
| `penalty_cost_per_year` | money (₹) | No | - |  |
| `error_cost_per_year` | money (₹) | No | - |  |

Optional fields you leave out are omitted from the call entirely, so the
calculator's own documented defaults apply.

## What the model assumes

Not a statutory calculation. It values the hours a payroll cycle takes at the loaded cost of the people doing it, nets off the software, and adds back the penalties and corrections that manual processing tends to produce.

Rates are data, not code: they live in dated tables with a cited source in
`crmleaf/payroll-core`, so a rate change is a new dated entry rather than an edit
to a constant.

> [!IMPORTANT]
> This package implements our reading of the applicable statutes and is provided
> without warranty. It is a calculation library, not tax advice. Verify against
> your own compliance obligations before relying on the output for statutory
> filing.

## Publishing

| Tag | Publishes |
|-----|-----------|
| `roi-calculator-config` | `config/roi-calculator.php` |
| `roi-calculator-views` | `resources/views/vendor/roi-calculator` |
| `roi-calculator-assets` | `public/vendor/roi-calculator` |

## Licence

[MIT](LICENSE) © CRMLeaf. Use it commercially, embed it, fork it.

[js]: https://github.com/crmleaf/payroll-js
