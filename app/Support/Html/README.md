# Vendored Form / HTML builder

This directory contains the source of `laravelcollective/html` v6.4 (MIT, see LICENSE.txt),
re-namespaced to `App\Support\Html`.

The upstream package is abandoned and only supports Laravel <= 10. The admin UI has ~350
`Form::` / `HTML::` calls in 35 Blade views, so the builder is kept in-tree and maintained here
instead of rewriting every form. Registered in `config/app.php` (provider + `Form`/`HTML` aliases).
