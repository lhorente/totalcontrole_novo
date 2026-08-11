<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Alterar Senha</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">Utilize uma senha longa e aleatória para manter sua conta segura.</p>

    <form method="POST" action="{{ route('user-password.update') }}">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label for="current_password">Senha Atual</label>
        <input id="current_password" type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
        @error('current_password', 'updatePassword')
          <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="password">Nova Senha</label>
        <input id="password" type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
        @error('password', 'updatePassword')
          <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmar Nova Senha</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
  </div>
</div>
