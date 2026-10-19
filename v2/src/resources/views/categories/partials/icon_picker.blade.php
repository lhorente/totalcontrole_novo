{{--
  Seletor de ícone (Font Awesome 5.11.2) para categorias.
  Uso: @include('categories.partials.icon_picker', ['value' => $category->icon_class ?? null])
  Grava o campo "icon_class" (vazio = sem ícone → NULL no controller).
--}}
@php
  $iconPickerValue = old('icon_class', $value ?? null);

  // [nome do ícone (sem "fa-"), rótulo, palavras-chave para busca]
  $iconPickerIcons = [
    // Alimentação
    ['shopping-cart', 'Mercado', 'supermercado compras carrinho'],
    ['shopping-basket', 'Cesta', 'feira mercado compras hortifruti'],
    ['utensils', 'Restaurante', 'refeicao comida almoco jantar talheres'],
    ['hamburger', 'Lanche', 'fast food hamburguer lanchonete'],
    ['pizza-slice', 'Pizza', 'delivery comida pizzaria'],
    ['coffee', 'Café', 'cafeteria padaria'],
    ['mug-hot', 'Café quente', 'cha cafe bebida'],
    ['ice-cream', 'Sorvete', 'sobremesa doce'],
    ['bread-slice', 'Padaria', 'pao cafe da manha'],
    ['apple-alt', 'Fruta', 'hortifruti feira saudavel'],
    ['lemon', 'Limão', 'hortifruti feira fruta'],
    ['carrot', 'Legumes', 'verdura hortifruti feira'],
    ['drumstick-bite', 'Açougue', 'carne frango churrasco'],
    ['fish', 'Peixe', 'peixaria frutos do mar aquario'],
    ['cheese', 'Laticínios', 'queijo frios'],
    ['wine-glass-alt', 'Vinho', 'bebida adega'],
    ['beer', 'Cerveja', 'bar bebida happy hour'],
    ['cocktail', 'Drinks', 'bar balada bebida festa'],
    ['birthday-cake', 'Aniversário', 'festa bolo comemoracao'],

    // Casa
    ['home', 'Casa', 'moradia lar aluguel residencia'],
    ['building', 'Condomínio', 'apartamento predio'],
    ['key', 'Aluguel', 'chave imovel'],
    ['couch', 'Móveis', 'sofa decoracao'],
    ['bed', 'Quarto', 'cama colchao'],
    ['bath', 'Banheiro', 'banho'],
    ['blender', 'Eletrodomésticos', 'liquidificador cozinha'],
    ['tools', 'Manutenção', 'reparo conserto ferramentas'],
    ['hammer', 'Reforma', 'obra construcao martelo'],
    ['paint-roller', 'Pintura', 'reforma tinta'],
    ['broom', 'Limpeza', 'faxina diarista vassoura'],
    ['seedling', 'Jardim', 'plantas jardinagem'],

    // Contas
    ['bolt', 'Energia', 'luz eletricidade conta'],
    ['lightbulb', 'Luz', 'energia lampada ideia'],
    ['tint', 'Água', 'saneamento conta'],
    ['fire', 'Gás', 'botijao conta'],
    ['wifi', 'Internet', 'banda larga wifi'],
    ['mobile-alt', 'Celular', 'telefone plano smartphone'],
    ['phone', 'Telefone', 'fixo ligacao'],
    ['file-invoice-dollar', 'Conta / Boleto', 'fatura cobranca pagamento'],
    ['receipt', 'Recibo', 'nota fiscal comprovante'],
    ['sync-alt', 'Assinatura', 'recorrente mensalidade streaming'],

    // Transporte
    ['car', 'Carro', 'automovel veiculo'],
    ['car-side', 'Carro (lateral)', 'automovel veiculo'],
    ['gas-pump', 'Combustível', 'gasolina posto etanol'],
    ['oil-can', 'Óleo', 'troca de oleo manutencao carro'],
    ['car-crash', 'Seguro do carro', 'sinistro batida acidente'],
    ['parking', 'Estacionamento', 'parking'],
    ['road', 'Pedágio', 'estrada rodovia'],
    ['traffic-light', 'Multa', 'transito semaforo detran'],
    ['bus', 'Ônibus', 'transporte publico'],
    ['subway', 'Metrô', 'transporte publico trem'],
    ['taxi', 'Táxi / App', 'uber 99 corrida aplicativo'],
    ['motorcycle', 'Moto', 'motocicleta'],
    ['bicycle', 'Bicicleta', 'bike ciclismo'],
    ['truck', 'Frete', 'mudanca entrega caminhao'],
    ['plane', 'Avião', 'passagem aerea voo viagem'],

    // Saúde e cuidados
    ['heartbeat', 'Saúde', 'bem estar'],
    ['medkit', 'Farmácia', 'saude remedios primeiros socorros'],
    ['pills', 'Remédios', 'farmacia medicamento'],
    ['stethoscope', 'Médico', 'consulta saude'],
    ['notes-medical', 'Plano de saúde', 'convenio exames'],
    ['hospital', 'Hospital', 'internacao saude'],
    ['ambulance', 'Emergência', 'pronto socorro saude'],
    ['tooth', 'Dentista', 'odontologia dente'],
    ['glasses', 'Ótica', 'oculos oculista oftalmologista'],
    ['syringe', 'Vacina / Exame', 'injecao laboratorio'],
    ['dumbbell', 'Academia', 'musculacao ginastica'],
    ['running', 'Esporte', 'corrida exercicio'],
    ['spa', 'Estética', 'massagem beleza spa'],
    ['cut', 'Cabeleireiro', 'barbeiro salao corte cabelo'],

    // Família
    ['baby', 'Bebê', 'filho fralda'],
    ['baby-carriage', 'Carrinho de bebê', 'bebe filho enxoval'],
    ['child', 'Criança', 'filho filha escola'],
    ['users', 'Família', 'pessoas casal'],
    ['user', 'Pessoal', 'eu individual'],
    ['female', 'Ela', 'mulher esposa'],
    ['male', 'Ele', 'homem marido'],
    ['heart', 'Casal', 'amor namoro'],
    ['ring', 'Casamento', 'alianca noivado'],

    // Pet
    ['paw', 'Pet', 'animal estimacao petshop'],
    ['dog', 'Cachorro', 'cao pet'],
    ['cat', 'Gato', 'pet felino'],
    ['bone', 'Ração', 'petshop pet osso'],

    // Educação
    ['graduation-cap', 'Educação', 'faculdade formatura escola'],
    ['school', 'Escola', 'colegio mensalidade'],
    ['book', 'Livros', 'leitura livraria'],
    ['chalkboard-teacher', 'Curso', 'aula professor treinamento'],
    ['language', 'Idiomas', 'ingles curso lingua'],
    ['pencil-alt', 'Material escolar', 'papelaria lapis'],

    // Lazer e viagem
    ['film', 'Cinema', 'filme'],
    ['tv', 'TV / Streaming', 'televisao netflix'],
    ['music', 'Música', 'show spotify'],
    ['ticket-alt', 'Ingressos', 'evento show teatro'],
    ['theater-masks', 'Teatro', 'cultura espetaculo'],
    ['gamepad', 'Games', 'jogos videogame'],
    ['futbol', 'Futebol', 'esporte bola'],
    ['umbrella-beach', 'Praia', 'ferias lazer'],
    ['suitcase-rolling', 'Viagem', 'mala ferias'],
    ['hotel', 'Hotel', 'hospedagem pousada'],
    ['passport', 'Passaporte', 'viagem internacional documento'],
    ['globe-americas', 'Internacional', 'viagem exterior mundo'],
    ['campground', 'Camping', 'acampamento'],
    ['camera', 'Fotografia', 'foto camera'],

    // Compras
    ['tshirt', 'Roupas', 'vestuario camiseta'],
    ['shoe-prints', 'Calçados', 'sapato tenis'],
    ['gem', 'Joias', 'acessorios bijuteria'],
    ['shopping-bag', 'Compras', 'loja sacola shopping'],
    ['store', 'Loja', 'comercio'],
    ['gift', 'Presentes', 'presente natal aniversario'],
    ['laptop', 'Eletrônicos', 'computador notebook tecnologia'],

    // Finanças
    ['wallet', 'Carteira', 'dinheiro'],
    ['credit-card', 'Cartão', 'cartao de credito debito'],
    ['money-bill-wave', 'Dinheiro', 'especie saque'],
    ['coins', 'Moedas', 'troco dinheiro'],
    ['piggy-bank', 'Poupança', 'reserva economia cofrinho'],
    ['chart-line', 'Investimentos', 'acoes bolsa rendimento'],
    ['university', 'Banco', 'instituicao financeira tarifa'],
    ['landmark', 'Impostos', 'governo tributo ipva iptu'],
    ['percentage', 'Juros / Taxas', 'tarifa encargo'],
    ['hand-holding-usd', 'Empréstimo', 'salario renda receber'],
    ['briefcase', 'Trabalho', 'salario empresa emprego'],
    ['exchange-alt', 'Transferência', 'pix ted doc'],
    ['shield-alt', 'Seguro', 'protecao'],
    ['balance-scale', 'Jurídico', 'advogado processo'],
    ['donate', 'Doação', 'caridade'],
    ['church', 'Igreja', 'dizimo oferta'],
    ['tag', 'Outros', 'etiqueta diversos geral'],
  ];

  // Nome do ícone atual (aceita "fas fa-car" e o formato antigo "fa fa-car")
  $iconPickerCurrentName = null;
  if ($iconPickerValue && preg_match('/\bfa-([a-z0-9-]+)/', $iconPickerValue, $m)) {
    $iconPickerCurrentName = $m[1];
  }
@endphp

<div class="form-group icon-picker" data-icon-picker>
  <label>Ícone</label>
  <input type="hidden" name="icon_class" value="{{ $iconPickerValue }}" data-icon-picker-input>

  <div class="d-flex align-items-center mb-2">
    <div class="icon-picker-preview mr-3" data-icon-picker-preview>
      @if ($iconPickerValue)
        <i class="{{ $iconPickerValue }}"></i>
      @else
        <span class="text-muted small">—</span>
      @endif
    </div>
    <div class="flex-grow-1">
      <div class="small text-muted" data-icon-picker-label>{{ $iconPickerValue ?: 'Sem ícone' }}</div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-icon-picker-clear>
      <i class="fas fa-ban mr-1"></i> Sem ícone
    </button>
  </div>

  <input type="search" class="form-control form-control-sm mb-2" placeholder="Buscar ícone (ex.: mercado, saúde, pet...)" data-icon-picker-search autocomplete="off">

  <div class="icon-picker-grid" data-icon-picker-grid>
    @foreach ($iconPickerIcons as [$name, $label, $keywords])
      @php
        $class  = 'fas fa-'.$name;
        $search = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($label.' '.$keywords.' '.$name));
      @endphp
      <button type="button"
              class="icon-picker-item {{ $iconPickerCurrentName === $name ? 'active' : '' }}"
              title="{{ $label }}"
              data-icon="{{ $class }}"
              data-search="{{ $search }}">
        <i class="{{ $class }}"></i>
        <span>{{ $label }}</span>
      </button>
    @endforeach
    <div class="icon-picker-empty text-muted small d-none" data-icon-picker-empty>Nenhum ícone encontrado.</div>
  </div>
</div>

@once
  @push('styles')
  <style>
    .icon-picker-preview {
      width: 48px; height: 48px; border-radius: 8px;
      border: 1px solid #ced4da; background: #f8f9fa;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; flex-shrink: 0;
    }
    .icon-picker-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
      gap: 6px; max-height: 280px; overflow-y: auto;
      padding: 6px; border: 1px solid #dee2e6; border-radius: 6px;
    }
    .icon-picker-item {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 8px 4px; border: 1px solid transparent; border-radius: 6px;
      background: transparent; color: #495057; cursor: pointer; min-height: 64px;
    }
    .icon-picker-item i { font-size: 20px; margin-bottom: 4px; }
    .icon-picker-item span {
      font-size: 11px; line-height: 1.2; text-align: center;
      max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .icon-picker-item:hover { background: #f1f3f5; border-color: #dee2e6; }
    .icon-picker-item.active { background: #007bff; border-color: #007bff; color: #fff; }
    .icon-picker-empty { grid-column: 1 / -1; padding: 12px; text-align: center; }
  </style>
  @endpush

  @push('scripts')
  <script>
    (function () {
      function normalize(text) {
        return (text || '').toString().toLowerCase()
          .normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
      }

      document.querySelectorAll('[data-icon-picker]').forEach(function (picker) {
        var input   = picker.querySelector('[data-icon-picker-input]');
        var preview = picker.querySelector('[data-icon-picker-preview]');
        var label   = picker.querySelector('[data-icon-picker-label]');
        var search  = picker.querySelector('[data-icon-picker-search]');
        var empty   = picker.querySelector('[data-icon-picker-empty]');
        var items   = picker.querySelectorAll('.icon-picker-item');

        function select(iconClass) {
          input.value = iconClass || '';
          preview.innerHTML = '';
          if (iconClass) {
            var i = document.createElement('i');
            i.className = iconClass;
            preview.appendChild(i);
          } else {
            preview.innerHTML = '<span class="text-muted small">—</span>';
          }
          label.textContent = iconClass || 'Sem ícone';
          items.forEach(function (item) {
            item.classList.toggle('active', item.getAttribute('data-icon') === iconClass);
          });
        }

        items.forEach(function (item) {
          item.addEventListener('click', function () {
            select(item.getAttribute('data-icon'));
          });
        });

        picker.querySelector('[data-icon-picker-clear]').addEventListener('click', function () {
          select(null);
        });

        search.addEventListener('input', function () {
          var terms = normalize(search.value).split(/\s+/).filter(Boolean);
          var visible = 0;
          items.forEach(function (item) {
            var haystack = item.getAttribute('data-search');
            var match = terms.every(function (t) { return haystack.indexOf(t) !== -1; });
            item.style.display = match ? '' : 'none';
            if (match) visible++;
          });
          empty.classList.toggle('d-none', visible > 0);
        });

        // Enter na busca não deve enviar o formulário
        search.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') e.preventDefault();
        });
      });
    })();
  </script>
  @endpush
@endonce
