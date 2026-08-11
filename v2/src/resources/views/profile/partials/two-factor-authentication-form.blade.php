@php
  $user = auth()->user();
  $twoFactorEnabled = ! is_null($user->two_factor_secret);
  $twoFactorConfirmed = $twoFactorEnabled && ! is_null($user->two_factor_confirmed_at);
@endphp

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Autenticação em Dois Fatores</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">A autenticação em dois fatores é obrigatória para acessar o sistema.</p>

    @if ($twoFactorConfirmed)
      <h5 class="text-success">A autenticação em dois fatores está ativa.</h5>
    @else
      <h5 class="text-danger">Você precisa ativar a autenticação em dois fatores para continuar usando o sistema.</h5>
    @endif

    <p class="text-muted">
      Com a autenticação em dois fatores ativa, você precisará informar um código gerado por um aplicativo autenticador (ex: Google Authenticator) a cada novo login.
    </p>

    @if (! $twoFactorEnabled)

      <button type="button" class="btn btn-primary" id="two-factor-enable-btn">Ativar</button>

      <form method="POST" action="{{ route('two-factor.enable') }}" id="two-factor-enable-form" class="d-none">
        @csrf
      </form>

      @push('scripts')
        <script>
          $('#two-factor-enable-btn').on('click', function () {
            confirmPassword(function () {
              $('#two-factor-enable-form')[0].submit();
            });
          });
        </script>
      @endpush

    @elseif (! $twoFactorConfirmed)

      <div class="mt-3">
        <p class="font-weight-bold">Escaneie o QR code abaixo com o aplicativo autenticador do seu celular e informe o código gerado para confirmar a ativação.</p>
        <div>{!! $qrCode !!}</div>
      </div>

      <form method="POST" action="{{ route('two-factor.confirm') }}" id="two-factor-confirm-form" class="mt-3">
        @csrf
        <div class="form-group" style="max-width: 20rem;">
          <label for="code">Código do aplicativo autenticador</label>
          <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" autocomplete="one-time-code" inputmode="numeric" autofocus>
          @error('code')
            <span class="invalid-feedback d-block">{{ $message }}</span>
          @enderror
        </div>
        <button type="button" class="btn btn-primary" id="two-factor-confirm-btn">Confirmar</button>
      </form>

      @push('scripts')
        <script>
          $('#two-factor-confirm-btn').on('click', function () {
            confirmPassword(function () {
              $('#two-factor-confirm-form')[0].submit();
            });
          });
        </script>
      @endpush

    @else

      <div id="recovery-codes-container" class="mt-3" style="display:none;">
        <p class="font-weight-bold text-muted">
          Guarde estes códigos de recuperação em um gerenciador de senhas seguro. Eles são a única forma de recuperar o acesso caso você perca o dispositivo com o aplicativo autenticador — não há recuperação por e-mail ou SMS.
        </p>
        <div class="bg-light p-3 rounded" style="font-family: monospace;" id="recovery-codes-list"></div>
      </div>

      <div class="mt-3">
        <button type="button" class="btn btn-secondary mr-2" id="show-recovery-codes-btn">Exibir Códigos de Recuperação</button>
        <button type="button" class="btn btn-secondary" id="regenerate-recovery-codes-btn">Gerar Novos Códigos de Recuperação</button>
      </div>

      @push('scripts')
        <script>
          function fetchRecoveryCodes() {
            $.ajax({
              url: '{{ route('two-factor.recovery-codes') }}',
              method: 'GET',
              headers: { 'Accept': 'application/json' },
              success: function (codes) {
                var $list = $('#recovery-codes-list').empty();
                codes.forEach(function (code) {
                  $('<div>').text(code).appendTo($list);
                });
                $('#recovery-codes-container').show();
              },
            });
          }

          $('#show-recovery-codes-btn').on('click', function () {
            confirmPassword(fetchRecoveryCodes);
          });

          $('#regenerate-recovery-codes-btn').on('click', function () {
            confirmPassword(function () {
              $.ajax({
                url: '{{ route('two-factor.recovery-codes') }}',
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                data: { _token: '{{ csrf_token() }}' },
                success: fetchRecoveryCodes,
              });
            });
          });
        </script>
      @endpush

    @endif
  </div>
</div>
