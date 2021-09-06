var TextEditorContainer = React.createClass({
    getInitialState: function () {
        return {
            text: "",
            page_no: 0,
            message: "",
            stateChange: false
        }
    },
    setStateChange: function (state) {
        this.stateChange = state;
    },
    getStateChange: function () {
        return this.stateChange;
    },
    loadText: function () {
        var self = this;
        self.setState({
            text: "",
            page_no: self.props.contractApp.getCurrentPage(),
            message: LANG.loading_pdf + self.props.contractApp.getCurrentPage()
        });
        if (this.xhr && this.xhr.readystate != 4) {
            //if the users clicks pagination quickly, abort previous ajax calls.
            this.xhr.abort();
        }
        this.xhr = $.ajax({
            url: this.props.loadApi,
            dataType: "json",
            type: "GET",
            data: {
                page: this.props.contractApp.getCurrentPage()
            }
        }).done(function (response) {
            self.props.contractApp.set({
                pdf_url: response.pdf
            });
            self.setState({
                text: response.message,
                page_no: self.props.contractApp.getCurrentPage(),
                message: ""
            });
        })
    },
    componentDidMount: function () {
        var self = this;
        this.props.contractApp.on("change:page_no", function () {
            self.loadText();
        });

        window.addEventListener("beforeunload", function (e) {
            var confirmationMessage = LANG.confirm_save;
            if (self.getStateChange()) {
                (e || window.event).returnValue = confirmationMessage;
                return confirmationMessage;
            }

        });


    },
    onChange: function (evt) {
        this.html = evt.target.value;
        this.setStateChange(true);
    },
    saveClicked: function () {
        var self = this;
        self.setStateChange(false);
        $.ajax({
            url: this.props.saveApi,
            data: {
                text: $('.text-annotator').html(),
                page: this.state.page_no
            },
            type: 'POST'
        }).success(function (response) {
            self.setState({message: LANG.text_saved});
            $('.text-annotator').html(response.message);
            $('.text-annotator').animate({scrollTop: $('.text-annotator').offset().top - $('.text-annotator').scrollTop()}, 'slow');

            $.ajax({
                url: self.props.publishApi,
                data: {
                    type : 'text'
                },
                type: 'POST'
            }).success(function(response){
            });
        });
    },
    sanitizeTxt: function (text) {
        //replace the <  and > with &lt;%gt if they are not one of the tags below
        text = text.replace(/(<)(\/?)(?=span|div|p|br)([^>]*)(>)/g, "----lt----$2$3----gt----");
        text = text.replace(/</g, "&lt;");
        text = text.replace(/>/g, "&gt;");
        text = text.replace(/----lt----/g, "<");
        text = text.replace(/----gt----/g, ">");
        return text;
    },
    render: function () {
        var text = this.sanitizeTxt(this.state.text);
        var message = '';
        if (this.state.message != '') {
            message = (<div className="alert alert-info">{this.state.message}</div>)
        }
        return (
            <div className="text-panel">
                {message}
                <TextEditor
                    onChange={this.onChange}
                    html={text}/>
                <button className="btn btn-primary" onClick={this.saveClicked}>{LANG.save}</button>
            </div>
        );
    }
});
var TextEditor = React.createClass({
    componentDidMount: function () {
        if (this.props.html !== React.findDOMNode(this).innerHTML) {
            React.findDOMNode(this).innerHTML = this.props.html;
        }
        $('div[contenteditable="true"]').keypress(function (event) {

            if (event.which != 13)
                return true;

            var docFragment = document.createDocumentFragment();

            var newEle = document.createTextNode('\n');
            docFragment.appendChild(newEle);

            newEle = document.createElement('br');
            docFragment.appendChild(newEle);

            var range = window.getSelection().getRangeAt(0);
            range.deleteContents();
            range.insertNode(docFragment);

            range = document.createRange();
            range.setStartAfter(newEle);
            range.collapse(true);

            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);

            return false;
        });
    },
    render: function () {
        return (
            <div
                className="text-annotator"
                onInput={this.emitChange}
                onBlue={this.emitChange}
                contentEditable="true"
                dangerouslySetInnerHTML={{__html: this.props.html}}>
            </div>
        );
    },
    emitChange: function (evt) {
        var html = React.findDOMNode(this).innerHTML;
        if (this.props.onChange && html != this.lastHtml) {
            evt.target = {value: html};
            this.props.onChange(evt);
        }
        this.lastHtml = html;
    }
});
