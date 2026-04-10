(function () {
    var opts = [
        { value: '',   label: window.translationSelectLabel },
        { value: 'en', label: 'English' },
        { value: 'es', label: 'Spanish' },
        { value: 'fr', label: 'French'  },
    ];

    function buildSelect() {
        var select = document.createElement('select');
        select.className = 'form-control';
        select.style.cssText = 'position:absolute; right:10px; top:50%; transform:translateY(-50%); width:auto; display:inline-block; height:30px;';

        opts.forEach(function (opt) {
            var option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.label;
            if (opt.value === translationVariant) option.selected = true;
            select.appendChild(option);
        });

        select.addEventListener('change', function () {
            var url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('translationLang', this.value);
            } else {
                url.searchParams.delete('translationLang');
            }
            window.location.href = url.toString();
        });

        return select;
    }

    function inject() {
        var headWrap = document.querySelector('.head-wrap');
        if (!headWrap) { setTimeout(inject, 100); return; }
        headWrap.style.position = 'relative';
        headWrap.appendChild(buildSelect());
    }

    inject();
})();
