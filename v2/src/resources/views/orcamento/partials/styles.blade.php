{{-- Mesmo visual do Nosso Mês / Nosso Ano (classes mr-*), mais a grade do orçamento (oc-*) --}}
<style>
  .mr-card { background: #fff; border-radius: .25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.15); }
  .mr-card-header { padding: 14px 20px; border-bottom: 1px solid #eef0f2; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .mr-card-title { font-size: 1rem; font-weight: 700; color: #343a40; margin: 0; display: flex; align-items: center; gap: 8px; }
  .mr-card-sub { font-size: .82rem; color: #8a94a3; margin-top: 2px; }
  .mr-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: .76rem; font-weight: 600; white-space: nowrap; }
  .mr-badge-tranquilo { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
  .mr-badge-atencao   { background: #FDF4E7; color: #8A5A1C; border: 1px solid #F0C77E; }
  .mr-badge-vermelho  { background: #FEF2F2; color: #9B1C1C; border: 1px solid #FCA5A5; }
  .mr-badge-planejado { background: #EFF6FF; color: #1D4A7C; border: 1px solid #93C5FD; }
  .mr-badge-sem_dados { background: #F1F3F5; color: #6c757d; border: 1px solid #dee2e6; }
  .oc-kpi { background: #fff; border-radius: .25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); padding: 14px 16px; height: 100%; }
  .oc-kpi-label { font-size: .82rem; color: #6c757d; }
  .oc-kpi-valor { font-size: 1.35rem; font-weight: 700; }
  .oc-kpi-sub { font-size: .76rem; color: #6c757d; }
  .oc-grid { font-size: .8rem; min-width: 1240px; margin: 0; }
  .oc-grid td, .oc-grid th { white-space: nowrap; padding: 5px 8px; vertical-align: middle; }
  .oc-grid .oc-sticky { position: sticky; left: 0; background: #fff; z-index: 1; }
  .oc-grid .oc-grupo td { background: #f4f6f9; font-weight: 700; }
  .oc-grid .oc-grupo .oc-sticky { background: #f4f6f9; }
  .oc-vazio { color: #ced4da; }
  .oc-acima { color: #1D4A7C; font-weight: 600; }
  .oc-abaixo { color: #065F46; }
  .oc-aberto { color: #8A5A1C; background: #FDF4E7; }
  .oc-futuro { color: #adb5bd; }
  .oc-meta-barra { position: relative; height: 12px; background: #eef0f2; border-radius: 6px; overflow: hidden; }
  .oc-meta-barra > span { position: absolute; top: 0; bottom: 0; }
</style>
