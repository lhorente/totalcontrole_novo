<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-body">
      <div class="row">
        <div class="btn-group col-md-12" role="group" aria-label="...">
          <button type="button" class="btn btn-default">Left</button>
          <button type="button" class="btn btn-default">Middle</button>
          <button type="button" class="btn btn-default">Right</button>
        </div>


        <div class="form-group col-md-12">
          <input name="data[parcelas][0][descricao]" class="form-control" placeholder="Informe a descrição do lançamento" type="text" id="Parcela0TransacaoDescricao">
        </div>

        <div class="form-group col-md-4">
          <label for="">Categoria*</label>
          <select name="data[Transacao][id_categoria]" class="form-control" placeholder="" id="TransacaoIdCategoria">
            <option value="">Selecione...</option>
            <option value="6">Alimentação</option>
            <option value="28">Cachorros</option>
            <option value="13">Casa</option>
            <option value="24">Combustível</option>
            <option value="5">Contas mensais</option>
            <option value="9">Economia</option>
            <option value="34">Empréstimos</option>
            <option value="4">Estudos</option>
            <option value="32">Furo no saldo</option>
            <option value="8">Lazer</option>
            <option value="29">Mercado</option>
            <option value="11">Outros</option>
            <option value="31">Presentes</option>
            <option value="10">Roupas</option>
            <option value="1">Salário</option>
            <option value="7">Saúde/Higiene</option>
            <option value="3">Veículos</option>
            <option value="2">Vendas</option>
            <option value="35">Viagens</option>
            <option value="27">Vídeo Game</option>
          </select>
        </div>


          <div class="form-group col-md-4">
            <label for="">Contato</label>
            <select name="data[Transacao][id_cliente]" class="form-control" placeholder="" id="TransacaoIdCliente">
              <option value="">Selecione...</option>
              <option value="16">Agência Sagu</option>
              <option value="30">Alfama</option>
              <option value="10">Amauri Alves</option>
              <option value="9">Angela Fscodata</option>
              <option value="25">Anselmo Paulista</option>
              <option value="2">Antonio Elero</option>
              <option value="15">Bete Soares</option>
              <option value="5">Bruna S2</option>
              <option value="11">Carlos Oliveira</option>
              <option value="8">Célio Lhorente</option>
              <option value="27">CITS</option>
              <option value="3">Everson</option>
              <option value="4">Honorio Lhorente</option>
              <option value="21">JMSystem</option>
              <option value="28">Joyce</option>
              <option value="14">K7 Cominucação</option>
              <option value="13">Kleber Taliba</option>
              <option value="7">Leandro Martinhuk</option>
              <option value="12">Midiaweb</option>
              <option value="26">Moura Pavimentação</option>
              <option value="23">Personaliza</option>
              <option value="32">Positivo</option>
              <option value="17">Prinnx</option>
              <option value="18">Priscila Roeder</option>
              <option value="6">Renan Gasparini</option>
              <option value="24">Thawan Soares</option>
              <option value="20">Thiago Agostinho</option>
              <option value="33">Tia Estela</option>
              <option value="31">Vó Yara</option>
              <option value="22">Wiverson Marques</option>
              <option value="29">Yhum</option>
            </select>
          </div>
            <div class="form-group col-md-4">
              <label for="">Cartão</label>
              <select name="data[Transacao][id_cartao]" class="form-control" placeholder="" id="TransacaoIdCartao">
                <option value="">Selecione...</option>
                <option value="1">Visa Bradesco</option>
                <option value="2">Mastercard Itaú</option>
                <option value="3">NuBank</option>
                <option value="4">Porto Seguro</option>
              </select>
            </div>
              <div class="form-group col-md-4">
                <label for="">Parcelamento</label>
                <select name="data[Transacao][parcelas]" class="form-control" placeholder="" id="TransacaoParcelas">
                  <option value="1">Nenhum</option>
                  <option value="2">2 meses</option>
                  <option value="3">3 meses</option>
                  <option value="4">4 meses</option>
                  <option value="5">5 meses</option>
                  <option value="6">6 meses</option>
                  <option value="7">7 meses</option>
                  <option value="8">8 meses</option>
                  <option value="9">9 meses</option>
                  <option value="10">10 meses</option>
                  <option value="11">11 meses</option>
                  <option value="12">12 meses</option>
                </select>
              </div>

                <div class="form-group col-md-4">
                  <label for="">Serviço</label>
                  <select name="data[Transacao][id_servico]" class="form-control" placeholder="" id="TransacaoIdServico">
                    <option value="">Selecione...</option>
                    <option value="43">Ajustes Plataforma</option>
                    <option value="39">Divesa</option>
                    <option value="42">efeitoesporte.com.br</option>
                    <option value="40">Formulário cobrevi.com.br</option>
                    <option value="19">Freelancer 01/07 até 03/07</option>
                    <option value="20">Freelancer 07/07 até 11/07</option>
                    <option value="15">Gonzales Y Garcia - Galeria de imagens</option>
                    <option value="38">Histórico DER</option>
                    <option value="24">Icone Sports - Simulador</option>
                    <option value="14">Leve sabor parte 1</option>
                    <option value="18">LV Soho</option>
                    <option value="30">Negocie - Setembro</option>
                    <option value="34">Plugin Relatorios</option>
                    <option value="35">Simplelog</option>
                    <option value="44">Site Alfama Alimentos</option>
                    <option value="41">Site Moura Pavimentação</option>
                    <option value="33">Tiempo</option>
                    <option value="21">Translog - Mapa de distribuição</option>
                    <option value="31">Translog - Setembro</option>
                  </select>
                </div>
                  <div class="form-group col-md-4">
                    <label for="">Carteira*</label>
                    <select name="data[Transacao][id_caixa]" class="form-control" placeholder="" id="TransacaoIdCaixa">
                      <option value="1">Conta</option>
                      <option value="2">Poupança</option>
                      <option value="3">Bruna</option>
                      <option value="4">Bruna (Contas)</option>
                      <option value="6">NuConta</option>
                      <option value="7">Veículos</option>
                      <option value="8">Cachorros</option>
                      <option value="9">Roupas</option>
                      <option value="10">Aniversários</option>
                      <option value="11">Reserva presentes</option>
                      <option value="12">Estudos</option>
                      <option value="13">Reformas/Casa</option>
                      <option value="14">Vídeo Game</option>
                      <option value="15">Viagem</option>
                      <option value="16">Gravidez Bruna</option>
                      <option value="17">Camboriú Casamento</option>
                      <option value="18">Reserva Emergência</option>
                      <option value="19">Easynvest</option>
                      <option value="20">Clear XP</option>
                      <option value="21">Cartão de crédito</option>
                      <option value="22">Reserva (Casa)</option>
                      <option value="23">Festa na Rede</option>
                    </select>
                  </div>
                  </div>
                  <div class="rows-parcelas">
                    <div class="row row-parcela">
                      <div class="form-group col-md-2">
                        <label for="">Data*</label>
                        <input name="data[parcelas][0][data]" class="form-control datepicker datepicker-readonly hasDatepicker" placeholder="" type="text" id="Parcela0TransacaoData" autocomplete="off" readonly="">
                      </div>
                      <div class="form-group col-md-2">
                        <label for="">Pagamento</label>
                        <input name="data[parcelas][0][data_pagamento]" class="form-control datepicker datepicker-readonly hasDatepicker" placeholder="" type="text" id="Parcela0TransacaoDataPagamento" autocomplete="off" readonly="">
                      </div>
                      <div class="form-group col-md-3">
                        <label for="">Valor*</label>
                        <input name="data[parcelas][0][valor]" class="form-control input-valor campo-formatado" placeholder="" type="tel" id="Parcela0TransacaoValor" autocomplete="off">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer justify-content-between">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary">Save changes</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
