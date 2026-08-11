<div class="card mt-3 border-danger">
  <div class="card-header">
    <h3 class="card-title text-danger">Excluir Conta</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">Ao excluir sua conta, todos os seus dados serão permanentemente removidos. Baixe qualquer informação que deseje manter antes de continuar.</p>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete-account-modal">
      Excluir Conta
    </button>
  </div>
</div>

<div class="modal fade" id="delete-account-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('current-user.destroy') }}">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Excluir Conta</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir sua conta? Esta ação é permanente. Digite sua senha para confirmar.</p>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Senha">
          @error('password')
            <span class="invalid-feedback d-block">{{ $message }}</span>
          @enderror
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Excluir Conta</button>
        </div>
      </form>
    </div>
  </div>
</div>
