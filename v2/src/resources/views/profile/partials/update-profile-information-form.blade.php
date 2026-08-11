<div class="card">
  <div class="card-header">
    <h3 class="card-title">Informações do Perfil</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">Atualize o nome e o e-mail da sua conta.</p>

    <form method="POST" action="{{ route('user-profile-information.update') }}">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label for="name">Nome</label>
        <input id="name" type="text" name="name" class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}">
        @error('name', 'updateProfileInformation')
          <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}">
        @error('email', 'updateProfileInformation')
          <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
  </div>
</div>
