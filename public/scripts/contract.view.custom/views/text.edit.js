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
            message: "Loading page" + self.props.contractApp.getCurrentPage()
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
            var confirmationMessage = "Please save your edits before exiting the page.";
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
                text: this.html,
                page: this.state.page_no
            },
            type: 'POST'
        }).done(function (response) {
            self.setState({message: "Successfully saved."});
            $('.text-editor').animate({scrollTop: $('.text-editor').offset().top - $('.text-editor').scrollTop()}, 'slow');
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
                <button className="btn btn-primary" onClick={this.saveClicked}>Save</button>
            </div>
        );
    }
});
var TextEditor = React.createClass({
    componentDidMount: function () {
        if (this.props.html !== React.findDOMNode(this).innerHTML) {
            React.findDOMNode(this).innerHTML = this.props.html;
        }
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
