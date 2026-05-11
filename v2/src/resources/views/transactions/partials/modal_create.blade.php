{{-- Quick Create Transaction Modal --}}
<div class="modal fade" id="modal-create-transaction" tabindex="-1" role="dialog" aria-labelledby="mcr-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      {{-- Header --}}
      <div class="met-header">
        <div>
          <h5 class="met-header-title" id="mcr-title">Novo lançamento</h5>
          <p class="met-header-subtitle" id="mcr-subtitle">Preencha os dados abaixo</p>
        </div>
        <button type="button" class="met-close-btn" data-dismiss="modal" aria-label="Fechar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      {{-- Form --}}
      <form id="form-mcr" method="POST" action="{{ route('transactions.storeModal') }}">
        @csrf
        <input type="hidden" name="_back" value="{{ request()->fullUrl() }}">
        <input type="hidden" name="id_categoria" id="mcr-id-categoria">

        <div class="met-body">

          {{-- Seção 1: Informações principais --}}
          <div class="met-section">
            <p class="met-section-label">Informações principais</p>

            <div class="met-field-group met-grid-2-1">
              <div class="met-form-group">
                <label class="met-label" for="mcr-descricao">Descrição</label>
                <input type="text" id="mcr-descricao" name="descricao" class="met-input" placeholder="Descrição do lançamento" autofocus>
              </div>
              <div class="met-form-group">
                <label class="met-label" for="mcr-valor">Valor</label>
                <div class="met-currency-wrap">
                  <span class="met-currency-prefix">R$</span>
                  <input type="number" id="mcr-valor" name="valor" class="met-input" step="0.01" min="0" placeholder="0,00">
                </div>
              </div>
            </div>

            <div class="met-field-group met-grid-2" style="margin-top:14px">
              <div class="met-form-group">
                <label class="met-label" for="mcr-data">Data</label>
                <input type="date" id="mcr-data" name="data" class="met-input">
              </div>
              <div class="met-form-group">
                <label class="met-label" for="mcr-tipo">Tipo</label>
                <select id="mcr-tipo" name="tipo" class="met-input">
                  <option value="despesa">Despesa</option>
                  <option value="lucro">Receita</option>
                  <option value="transferencia">Transferência</option>
                  <option value="investimento">Investimento</option>
                  <option value="emprestimo">Empréstimo</option>
                  <option value="pagamento_emprestimo">Pgto. Empréstimo</option>
                </select>
              </div>
            </div>
          </div>

          {{-- Seção 2: Categoria --}}
          <div class="met-section">
            <p class="met-section-label">Categoria</p>
            <div class="met-quick-buttons" id="mcr-quick-buttons">
              <button type="button" class="met-quick-btn active" data-value="">Sem categoria</button>
              @foreach ($categorias as $cat)
                <button type="button" class="met-quick-btn" data-value="{{ $cat->id }}">
                  @if ($cat->icon_class)
                    <i class="{{ $cat->icon_class }}"></i>
                  @endif
                  {{ $cat->nome }}
                </button>
              @endforeach
            </div>
          </div>

          {{-- Seção 3: Contexto --}}
          <div class="met-section">
            <div class="met-section-header">
              <p class="met-section-label">Contexto</p>
              <span class="met-badge-optional">Opcional</span>
            </div>
            <div class="met-field-group met-grid-2">
              <div class="met-form-group">
                <label class="met-label" for="mcr-id-cartao">Cartão</label>
                <select id="mcr-id-cartao" name="id_cartao" class="met-input">
                  <option value="">Nenhum</option>
                  @foreach ($cartoes as $c)
                    <option value="{{ $c->id }}">{{ $c->descricao }}</option>
                  @endforeach
                </select>
              </div>
              <div class="met-form-group">
                <label class="met-label" for="mcr-id-cliente">Pessoa</label>
                <select id="mcr-id-cliente" name="id_cliente" class="met-input">
                  <option value="">Nenhuma</option>
                  @foreach ($pessoas as $p)
                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          {{-- Seção 4: Status do pagamento --}}
          <div class="met-section">
            <div class="met-payment-section">
              <p class="met-payment-title">Status do pagamento</p>
              <div class="met-field-group met-grid-2">
                <div class="met-form-group">
                  <label class="met-label" for="mcr-data-pagamento">Data de pagamento</label>
                  <input type="date" id="mcr-data-pagamento" name="data_pagamento" class="met-input">
                </div>
                <div class="met-form-group" style="display:flex; align-items:flex-end; padding-bottom:2px">
                  <label class="met-checkbox-label">
                    <input type="checkbox" id="mcr-marcar-pago">
                    Marcar como pago
                  </label>
                </div>
              </div>
              <p class="met-helper-text">Deixe vazio se ainda não foi pago</p>
            </div>
          </div>

        </div>{{-- /.met-body --}}

        {{-- Footer --}}
        <div class="met-footer" style="justify-content:flex-end">
          <div class="met-footer-right">
            <button type="button" class="met-btn-cancel" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="met-btn-save">
              <i class="fas fa-plus"></i> Adicionar
            </button>
          </div>
        </div>

      </form>

    </div>{{-- /.modal-content --}}
  </div>{{-- /.modal-dialog --}}
</div>
