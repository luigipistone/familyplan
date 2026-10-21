const n = (value) => Math.max(0, Number(value) || 0);
const cents = (value) => Math.round(n(value) * 100);
const multiplier = (scope, quantity) => scope === 'per_piece' ? quantity : 1;

export function calculatePrintCost(data) {
  const quantity = Math.max(1, Math.trunc(n(data.quantity) || 1));
  const filament = { model: 0, support: 0, purge: 0, waste: 0, total: 0 };
  for (const row of data.filaments || []) {
    const perGram = n(row.spool_grams) > 0 ? cents(row.spool_cost) / n(row.spool_grams) : 0;
    const waste = n(row.waste_percentage) / 100;
    for (const [field, key] of [['model_grams', 'model'], ['support_grams', 'support'], ['purge_grams', 'purge']]) {
      const base = Math.round(n(row[field]) * quantity * perGram);
      filament[key] += base;
      filament.waste += Math.round(base * waste);
    }
  }
  filament.total = filament.model + filament.support + filament.purge + filament.waste;
  const minutes = n(data.print_hours) * 60 + n(data.print_minutes);
  const hours = minutes / 60 * quantity;
  const energy = Math.round(n(data.average_watts) / 1000 * hours * cents(data.energy_cost_kwh));
  const machine = Math.round(hours * cents(data.machine_rate_hour));
  const labor = (data.labor || []).reduce((sum, row) => sum + Math.round(n(row.minutes) / 60 * cents(row.hourly_rate) * multiplier(row.scope, quantity)), 0);
  const additional = (data.additional_costs || []).reduce((sum, row) => sum + Math.round(n(row.quantity) * cents(row.unit_cost) * multiplier(row.scope, quantity)), 0);
  const base = filament.total + energy + machine + labor + additional;
  const risk = Math.min(90, n(data.failure_percentage));
  const total = risk ? Math.round(base / (1 - risk / 100)) : base;
  const percentage = n(data.pricing_percentage);
  const marked = data.pricing_mode === 'margin' && percentage < 100 ? Math.round(total / (1 - percentage / 100)) : Math.round(total * (1 + percentage / 100));
  const vatRate = n(data.vat_percentage) / 100;
  let net = marked; let vat = 0; let final = marked;
  if (data.vat_included && vatRate) { final = marked; net = Math.round(final / (1 + vatRate)); vat = final - net; }
  else { vat = Math.round(net * vatRate); final = net + vat; }
  const rounding = data.rounding;
  if (rounding === 'half') final = Math.round(final / 50) * 50;
  if (rounding === 'euro') final = Math.round(final / 100) * 100;
  if (rounding === '90') final = Math.max(90, Math.ceil((final - 90) / 100) * 100 + 90);
  if (rounding === '99') final = Math.max(99, Math.ceil((final - 99) / 100) * 100 + 99);
  vat = Math.max(0, final - net);
  return { quantity, filament, energy_cents: energy, machine_cents: machine, labor_cents: labor, additional_cents: additional, base_cost_cents: base, risk_cost_cents: total - base, total_cost_cents: total, cost_per_piece_cents: Math.round(total / quantity), net_price_cents: net, vat_cents: vat, final_price_cents: final, profit_cents: Math.max(0, net - total), price_per_piece_cents: Math.round(final / quantity) };
}
