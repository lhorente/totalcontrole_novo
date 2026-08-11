@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Meu Perfil</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Meu Perfil</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    @if (session('status'))
      <div class="alert alert-success">
        {{ match (session('status')) {
            'profile-information-updated' => 'Informações do perfil atualizadas com sucesso.',
            'password-updated' => 'Senha atualizada com sucesso.',
            'two-factor-authentication-enabled' => 'Autenticação em dois fatores ativada. Escaneie o QR code abaixo para confirmar.',
            'two-factor-authentication-confirmed' => 'Autenticação em dois fatores confirmada com sucesso.',
            'recovery-codes-generated' => 'Novos códigos de recuperação gerados.',
            'other-browser-sessions-closed' => 'Sessões em outros navegadores encerradas.',
            default => session('status'),
        } }}
      </div>
    @endif

    @include('profile.partials.update-profile-information-form')

    @include('profile.partials.update-password-form')

    @include('profile.partials.two-factor-authentication-form')

    @include('profile.partials.logout-other-browser-sessions-form')

    @include('profile.partials.delete-user-form')

  </div>
</div>

<!-- Modal de confirmação de senha (usado pelas ações de 2FA) -->
<div class="modal fade" id="password-confirm-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirme sua senha</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Por segurança, confirme sua senha para continuar.</p>
        <input type="password" id="password-confirm-input" class="form-control" placeholder="Senha">
        <span class="invalid-feedback d-block" id="password-confirm-error" style="display:none"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="password-confirm-submit">Confirmar</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function confirmPassword(onConfirmed) {
    var $modal = $('#password-confirm-modal');
    var $input = $('#password-confirm-input');
    var $error = $('#password-confirm-error');

    $input.val('');
    $error.hide().text('');
    $modal.modal('show');

    $('#password-confirm-submit').off('click').on('click', function () {
      $.ajax({
        url: '{{ route('password.confirm') }}',
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        data: {
          password: $input.val(),
          _token: '{{ csrf_token() }}',
        },
        success: function () {
          $modal.modal('hide');
          onConfirmed();
        },
        error: function (xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.password)
              ? xhr.responseJSON.errors.password[0]
              : 'Senha incorreta.';
          $error.text(msg).show();
        },
      });
    });

    $input.off('keyup').on('keyup', function (e) {
      if (e.key === 'Enter') {
        $('#password-confirm-submit').click();
      }
    });
  }
</script>
@endpush
@endsection
