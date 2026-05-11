{{-- Quick Edit Transaction Modal --}}
<div class="modal fade" id="modal-edit-transaction" tabindex="-1" role="dialog" aria-labelledby="met-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      {{-- Header --}}
      <div class="met-header">
        <div>
          <h5 class="met-header-title" id="met-title">Editar transação</h5>
          <p class="met-header-subtitle" id="met-subtitle"></p>
        </div>
        <button type="button" class="met-close-btn" data-dismiss="modal" aria-label="Fechar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      {{-- Form --}}
      <form id="form-met" method="POST" action="">
        @csrf
        <input type="hidden" name="_back" value="{{ request()->fullUrl() }}">
        <input type="hidden" name="id_workspace"     id="met-id-workspace">
        <input type="hidden" name="data_recebimento" id="met-data-recebimento">
        <input type="hidden" name="id_categoria"     id="met-id-categoria">

        <div class="met-body">

          {{-- Seção 1: Informações principais --}}
          <div class="met-section">
            <p class="met-section-label">Informações principais</p>

            <div class="met-field-group met-grid-2-1">
              <div class="met-form-group">
                <label class="met-label" for="met-descricao">Descrição</label>
                <input type="text" id="met-descricao" name="descricao" class="met-input" placeholder="Descrição do lançamento">
              </div>
              <div class="met-form-group">
                <label class="met-label" for="met-valor">Valor</label>
                <div class="met-currency-wrap">
                  <span class="met-currency-prefix">R$</span>
                  <input type="number" id="met-valor" name="valor" class="met-input" step="0.01" min="0">
                </div>
              </div>
            </div>

            <div class="met-field-group met-grid-2" style="margin-top:14px">
              <div class="met-form-group">
                <label class="met-label" for="met-data">Data</label>
                <input type="date" id="met-data" name="data" class="met-input">
              </div>
              <div class="met-form-group">
                <label class="met-label" for="met-tipo">Tipo</label>
                <select id="met-tipo" name="tipo" class="met-input">
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
            <div class="met-quick-buttons" id="met-quick-buttons">
              <button type="button" class="met-quick-btn" data-value="">Sem categoria</button>
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
                <label class="met-label" for="met-id-cartao">Cartão</label>
                <select id="met-id-cartao" name="id_cartao" class="met-input">
                  <option value="">Nenhum</option>
                  @foreach ($cartoes as $c)
                    <option value="{{ $c->id }}">{{ $c->descricao }}</option>
                  @endforeach
                </select>
              </div>
              <div class="met-form-group">
                <label class="met-label" for="met-id-cliente">Pessoa</label>
                <select id="met-id-cliente" name="id_cliente" class="met-input">
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
                  <label class="met-label" for="met-data-pagamento">Data de pagamento</label>
                  <input type="date" id="met-data-pagamento" name="data_pagamento" class="met-input">
                </div>
                <div class="met-form-group" style="display:flex; align-items:flex-end; padding-bottom:2px">
                  <label class="met-checkbox-label">
                    <input type="checkbox" id="met-marcar-pago">
                    Marcar como pago
                  </label>
                </div>
              </div>
              <p class="met-helper-text">Deixe vazio se ainda não foi pago</p>
            </div>
          </div>

        </div>{{-- /.met-body --}}

        {{-- Footer --}}
        <div class="met-footer">
          <button type="button" class="met-btn-delete" id="met-btn-delete">
            <i class="fas fa-trash"></i> Excluir
          </button>
          <div class="met-footer-right">
            <a href="#" id="met-view-link" class="met-btn-view" title="Abrir página completa de edição">
              <i class="fas fa-external-link-alt"></i>
            </a>
            <button type="button" class="met-btn-cancel" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="met-btn-save">
              <i class="fas fa-check"></i> Salvar
            </button>
          </div>
        </div>

      </form>

    </div>{{-- /.modal-content --}}
  </div>{{-- /.modal-dialog --}}
</div>

{{-- Form separado para exclusão --}}
<form id="form-met-delete" method="POST" action="" style="display:none">
  @csrf
  @method('DELETE')
  <input type="hidden" name="_back" value="{{ request()->fullUrl() }}">
</form>
