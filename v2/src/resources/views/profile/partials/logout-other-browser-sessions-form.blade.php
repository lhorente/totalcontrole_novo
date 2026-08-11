<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Sessões Ativas</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">Gerencie e encerre suas sessões ativas em outros navegadores e dispositivos.</p>

    @if ($sessions->count())
      <ul class="list-unstyled mb-4">
        @foreach ($sessions as $session)
          <li class="mb-2">
            <i class="fas {{ $session->agent['is_desktop'] ? 'fa-desktop' : 'fa-mobile-alt' }} mr-2 text-muted"></i>
            {{ $session->agent['platform'] }} - {{ $session->agent['browser'] }} —
            {{ $session->ip_address }},
            @if ($session->is_current_device)
              <span class="text-success font-weight-bold">Este dispositivo</span>
            @else
              ativo por último {{ $session->last_active }}
            @endif
          </li>
        @endforeach
      </ul>
    @endif

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#logout-sessions-modal">
      Encerrar Outras Sessões
    </button>
  </div>
</div>

<div class="modal fade" id="logout-sessions-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('other-browser-sessions.destroy') }}">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Encerrar Outras Sessões</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>Digite sua senha para confirmar o encerramento das sessões em outros dispositivos.</p>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Senha">
          @error('password')
            <span class="invalid-feedback d-block">{{ $message }}</span>
          @enderror
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Encerrar Outras Sessões</button>
        </div>
      </form>
    </div>
  </div>
</div>
