// Preenchimento assistido: o cliente sempre pode corrigir os campos antes de confirmar.
(() => {
  const cep = document.querySelector('input[name="cep"]');
  const kind = document.getElementById('tipo-entrega');
  const status = document.getElementById('cep-status');
  if (!cep || !kind || !status) return;

  const fields = Object.fromEntries(['rua', 'bairro', 'cidade', 'estado'].map(name => [name, document.querySelector(`input[name="${name}"]`)]));
  let timer;
  let currentRequest;
  let lastSearched = '';
  const say = message => { status.textContent = message; };

  async function lookup() {
    const digits = cep.value.replace(/\D/g, '');
    currentRequest?.abort();
    if (kind.value !== 'delivery' || digits.length !== 8) {
      lastSearched = '';
      say(digits.length && digits.length !== 8 ? 'Digite os 8 números do CEP.' : '');
      return;
    }
    if (digits === lastSearched) return;
    const request = new AbortController();
    currentRequest = request;
    lastSearched = digits;
    say('Buscando endereço...');
    try {
      const response = await fetch(`https://viacep.com.br/ws/${digits}/json/`, {
        signal: request.signal,
        headers: { Accept: 'application/json' }
      });
      if (!response.ok) throw new Error('Serviço indisponível');
      const data = await response.json();
      if (request.signal.aborted || cep.value.replace(/\D/g, '') !== digits) return;
      if (data.erro) {
        say('CEP não encontrado. Confira o número ou preencha o endereço manualmente.');
        return;
      }
      const values = { rua: data.logradouro, bairro: data.bairro, cidade: data.localidade, estado: data.uf };
      for (const [name, input] of Object.entries(fields)) {
        if (input && values[name]) input.value = values[name];
      }
      say('Endereço encontrado. Confira os dados e preencha o número.');
      document.querySelector('input[name="numero"]')?.focus();
    } catch (error) {
      if (error.name !== 'AbortError') {
        lastSearched = '';
        say('Não foi possível consultar o CEP. Preencha os campos manualmente.');
      }
    }
  }

  cep.addEventListener('input', () => {
    const digits = cep.value.replace(/\D/g, '').slice(0, 8);
    cep.value = digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
    clearTimeout(timer);
    currentRequest?.abort();
    if (digits.length !== 8) lastSearched = '';
    timer = setTimeout(lookup, 350);
  });
  cep.addEventListener('blur', () => { clearTimeout(timer); lookup(); });
  kind.addEventListener('change', () => { if (kind.value === 'delivery') lookup(); else { currentRequest?.abort(); say(''); } });
})();
