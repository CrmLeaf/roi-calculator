/*
 * @crmleaf/roi-calculator - a re-export, not a reimplementation.
 *
 * The arithmetic lives once, in @crmleaf/payroll-js, so a slab change cannot
 * land in one package and miss another. This package exists so a project that
 * only wants Payroll ROI Calculator can install only Payroll ROI Calculator and still get the
 * identical function it would have got from the suite.
 */

export { roi, roi as calculate, Money } from '@crmleaf/payroll-js';

export { roi as default } from '@crmleaf/payroll-js';
