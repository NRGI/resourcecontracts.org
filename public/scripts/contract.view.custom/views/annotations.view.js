var AnnotationHeader = React.createClass({
    componentDidMount: function() {
        var self = this;
        this.props.annotationsCollection.on("reset", function() {
          self.forceUpdate();
        });
    },
    render: function() {
        var count = this.props.annotationsCollection.length;
        return (
            <div className="annotation-title">{count} Annotations</div>
        );
    }
});

var AnnotationItem = React.createClass({
    getInitialState: function() {
        return {
            maxWords: 10,
            showEllipse: false,
            showMoreFlag: false,
            annotationType: "",
            text: ""
        }
    },
    componentDidMount: function() {
        var self = this;
        function getText(annotation) {
            //return text + quote if both present, else either text or quote
            var text = (annotation.get('text') ||  "") + "";
            var quote = (annotation.get('quote')  || "") + "";
            if(text && quote) {
                return text.trim() + " - " + quote.trim();
            }
            if(text && text.trim()) {
                return text.trim();
            }
            if(quote && quote.trim()) {
                return quote.trim();
            }
            return "";
        }
        function shallShowEllipse(text) {
            var words = (text + "").split(' ');
            if(words.length > self.state.maxWords) {
                return true;
            }
            return false;
        }
        function truncate(text) {
            var words = (text + "").split(" ");
            words = words.splice(0, self.state.maxWords);
            return words.join(" ");
        }
        var cluster = (this.props.annotation.get('cluster'))?this.props.annotation.get('cluster'):"Other";
        var category = this.props.annotation.get('category');
        var categoryEn = category.split("//")[0];
        var categoryFr = category.split("//")[1];
        var id = this.props.annotation.get('id');
        var text = getText(this.props.annotation);
        var preamble = text.split("--")[1] || '';
          text = text.split("--")[0];
        var showEllipse = shallShowEllipse(text);
        var pageNo = this.props.annotation.get('page_no');
        var shortText = "";
        if(showEllipse) {
            shortText = truncate(text);
        }
        var annotationType = "text";
        if(this.props.annotation.get('shapes')) {
            annotationType = "pdf";
        }
        var highlight = (this.props.contractApp.getSelectedAnnotation() === id)?true:false;
        var showMoreFlag = (this.props.contractApp.getSelectedAnnotation() === id)?true:false;
        this.setState({
            id: id,
            text: text,
            preamble:preamble,
            cluster: cluster,
            shortText: shortText,
            categoryEn: categoryEn,
            categoryFr: categoryFr,
            pageNo: pageNo,
            annotationType: annotationType,
            showEllipse: showEllipse,
            highlight: highlight,
            showMoreFlag: showMoreFlag
        });
        this.props.contractApp.on("annotations:highlight", function(annotation) {
            if(annotation.id === self.state.id) {
                self.setState({
                    showMoreFlag: true,
                    highlight: true
                });
                location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
            } else {
                self.setState({
                    showMoreFlag: false,
                    highlight: false
                });
            }
        });
        this.props.contractApp.on("change:selected_annotation_id-1", function() {
            if(self.props.contractApp.getSelectedAnnotation() === self.state.id) {
                self.setState({
                    showMoreFlag: true,
                    highlight: true
                });
                location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
            } else {
                self.setState({
                    showMoreFlag: false,
                    highlight: false
                });
            }
        });
    },
    handleAnnotationClick: function(e) {
        var self = this;
        e.preventDefault();
        switch(this.state.annotationType) {
            case "pdf":
                this.props.contractApp.setView("pdf");
                location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                this.props.contractApp.setSelectedAnnotation(self.state.id);
                this.props.contractApp.trigger("annotations:highlight", {id: self.state.id});
                this.props.contractApp.setCurrentPage(self.state.pageNo);
                this.props.contractApp.triggerUpdatePdfPaginationPage(self.state.pageNo);
                // this.props.contractApp.trigger("annotationHighlight", this.props.annotation.attributes);
                break
            case "text":
                location.hash = "#/text/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                this.props.contractApp.trigger("annotations:highlight", {id: self.state.id});
                this.props.contractApp.setView("text");
                this.props.contractApp.setCurrentPage(self.state.pageNo);
                setTimeout(this.props.contractApp.triggerScrollToTextPage());
                break;
        }
    },
    handleEllipsis: function(e) {
        e.preventDefault();
        var text = e.target.innerHTML;
        this.setState({showMoreFlag: !this.state.showMoreFlag});
    },
    render: function() {
        var currentAnnotationClass = (this.state.highlight)?"annotation-item selected-annotation":"annotation-item";
        var ellipsistext = "";
        var showText = this.state.text;
        if(this.state.showEllipse) {
            showText = this.state.text;
            ellipsistext = " ... less";
            if(!this.state.showMoreFlag) {
                ellipsistext = " ... more";
                showText = this.state.shortText;
            }
        }
        if (this.props.prevAnnotation === undefined || this.props.showClusterAnyway) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="header annotation-cluster">{this.state.cluster}</span>
                    <span className="link annotation-category-en"><a href="#" onClick={this.handleAnnotationClick}>{this.state.categoryEn}</a></span>
                    <span className="link annotation-category-fr" onClick={this.handleAnnotationClick}>{this.state.categoryFr}</span>
                    <span className="annotation-item-content">{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="annotation-item-preamble">{this.state.preamble}</span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page {this.state.pageNo}</span>
                </div>
            );
        } else if (this.props.annotation.attributes.category_key !== this.props.prevAnnotation.attributes.category_key) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="link annotation-category-en"><a href="#" onClick={this.handleAnnotationClick}>{this.state.categoryEn}</a></span>
                    <span className="link annotation-category-fr" onClick={this.handleAnnotationClick}>{this.state.categoryFr}</span>
                    <span className="annotation-item-content" >{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="annotation-item-preamble">{this.state.preamble}</span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page {this.state.pageNo}</span>
                </div>
            );
        } else if (this.props.annotation.attributes.text !== this.props.prevAnnotation.attributes.text) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="annotation-item-content">{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="annotation-item-preamble">{this.state.preamble}</span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page {this.state.pageNo}</span>
                </div>
            );
        } else {
            return (
                   <span id={this.state.id} className="link annotation-item-page" onClick={this.handleAnnotationClick}>, {this.state.pageNo}</span>
            );
        }
        // return (
        //     <div className={currentAnnotationClass} id={this.state.id}>
        //         <span>{this.state.cluster}</span>
        //         <span className="link annotation-category-en"><a href="#" onClick={this.handleAnnotationClick}>{this.state.categoryEn}</a></span>
        //         <span className="link annotation-category-fr" onClick={this.handleAnnotationClick}>{this.state.categoryFr}</span>
        //         <span className="annotation-item-content" >{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
        //         <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page: {this.state.pageNo}</span>
        //     </div>
        // );
    }
});

var AnnotationsSort = React.createClass({
    getInitialState: function() {
        return {
            show: false,
            sortBy: "cluster"
        }
    },
    componentDidMount: function() {
        var self = this;
        this.props.annotationsCollection.on("reset", function() {
            if(self.props.annotationsCollection.models.length > 0) {
                self.setState({show: true});
            }
        });
        this.setState({sortBy: "cluster"});
    },
    onClickPage: function(e) {
        e.preventDefault();
        this.props.annotationsCollection.setSortByKey("page_no");
        this.props.contractApp.resetSelectedAnnotation();
        this.props.contractApp.trigger("annotations:render");
        this.setState({sortBy: "page_no"});
    },
    onClickTopic: function(e) {
        e.preventDefault();
        this.props.annotationsCollection.setSortByKey("category");
        this.props.contractApp.resetSelectedAnnotation();
        this.props.contractApp.trigger("annotations:render");
        this.setState({sortBy: "cluster"});
    },
    render: function() {
        var topicList = "";
        var pageClassName = "active", topicClassName = "";
        if(this.state.sortBy == "cluster") {
            pageClassName = "";
            topicClassName = "active";
            topicList = <AnnotationTopicList contractApp={this.props.contractApp} />
        }
        var activeClass = this.state.sortBy;
        if(this.state.show) {
            return (
                <div className="annotation-sort">
                    <span className={pageClassName} onClick={this.onClickPage}>By Page</span>
                    <span className={topicClassName} onClick={this.onClickTopic}>By Topic</span>
                    {topicList}
                </div>
            );
        } else {
            return (<div></div>);
        }
    }
});

var AnnotationTopicList = React.createClass({
    getInitialState: function() {
        return {
            show: false
        };
    },
    handleClick: function(e) {
        e.preventDefault();
        this.props.contractApp.trigger("annotations:scroll-to-cluster", e.target.innerHTML);
        $(".annotations-topic-list > span").removeClass("selected-topic");
        $(e.target).addClass("selected-topic");
    },
    render: function() {
        return (
            <div className="annotations-topic-list">
                <span onClick={this.handleClick}>General</span>
                <span onClick={this.handleClick}>Environment</span>
                <span onClick={this.handleClick}>Fiscal</span>
                <span onClick={this.handleClick}>Operations</span>
                <span onClick={this.handleClick}>Social</span>
                <span onClick={this.handleClick}>Other</span>
            </div>
        );
    }
});
var AnnotationsList = React.createClass({
    getInitialState: function() {
        return {
            message: "Loading annotations..."
        }
    },
    componentDidMount: function() {
        var self = this;
        this.props.annotationsCollection.on("reset", function() {
            if(self.props.annotationsCollection.models.length > 0) {
                self.setState({message:""});
            } else {
                self.setState({message:"There are no annotations associated with this contract."});
            }
            if(self.props.contractApp.getSelectedAnnotation()) {
                self.props.contractApp.trigger("annotations:scroll-to-selected-annotation");
            }
        });
        this.props.contractApp.on("annotations:render", function(sortBy) {
            self.forceUpdate();
        });
        this.props.contractApp.on("annotations:highlight", function(annotation) {
            setTimeout(self.scrollToAnnotation(annotation.id), 1000);
        });
        this.props.contractApp.on("annotations:scroll-to-selected-annotation", function() {
            self.scrollToAnnotation(self.props.contractApp.getSelectedAnnotation());
        });
        this.props.contractApp.on("annotations:scroll-to-top", function() {
            self.scrollToTop();
        });
        this.props.contractApp.on("annotations:scroll-to-cluster", function(cluster) {
            self.scrollToCluster(cluster);
        });
    },
    scrollToCluster: function(cluster) {
        if($('#'+cluster).offset()) {
            var pageOffsetTop = $('#'+cluster).offset().top;
            var parentTop = $('.annotation-inner-viewer').scrollTop();
            var parentOffsetTop = $('.annotation-inner-viewer').offset().top
            $('.annotation-inner-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop},200);
        }
    },
    scrollToAnnotation: function(annotation_id) {
        if(annotation_id) {
            var pageOffsetTop = $('#'+annotation_id).offset().top;
            var parentTop = $('.annotation-inner-viewer').scrollTop();
            var parentOffsetTop = $('.annotation-inner-viewer').offset().top
            $('.annotation-inner-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop},200);
            this.props.contractApp.resetSelectedAnnotation();
        }
    },
    scrollToTop: function(e) {
        e.preventDefault();
        $('.annotation-inner-viewer').animate({scrollTop: 0}, 500);
    },
    getAnnotationItemsComponent: function(annotationsCollectionForList, showClusterAnyway) {
        var annotationsList = [];
        if(annotationsCollectionForList.models.length > 0) {
            for(var i = 0;i < annotationsCollectionForList.models.length; i++) {
                annotationsList.push((<AnnotationItem
                                showClusterAnyway={showClusterAnyway}
                                key={annotationsCollectionForList.models[i].get("id")}
                                contractApp={this.props.contractApp}
                                prevAnnotation={annotationsCollectionForList.models[i-1]}
                                annotation={annotationsCollectionForList.models[i]} />
                                ));
            }
        }
        return annotationsList;
    },
    sortByPage: function() {
        if(this.props.annotationsCollection.models.length > 0) {
            this.props.annotationsCollection.sort();
            return (
              <div className="annotations-list" id="id-annotations-list">
                {this.getAnnotationItemsComponent(this.props.annotationsCollection, true)}
                <div className="annotations-list-footer">
                    <a href={this.props.contractApp.getAnnotationsListAnchor()}>See all Annotations</a>
                </div>
              </div>
            );
        }
        return [];
    },
    sortByCluster: function() {
        var annotationsList = [];
        var self = this;
        this.props.annotationsCollection.sort();
        var clusters = ["General", "Environment", "Fiscal", "Operations", "Social", "Other"];
        _.map(clusters, function(cluster) {
            var filtered = self.props.annotationsCollection.filter(function(model) {
                return model.get("cluster") === cluster;
            });
            if(filtered.length) {
                var newCol = new AnnotationsCollection(filtered);
                annotationsList.push(<div id={cluster} key={cluster}>{self.getAnnotationItemsComponent(newCol, false)}</div>);
            }
        });
        return (
          <div className="annotations-list" id="id-annotations-list">
            {annotationsList}
            <div className="annotations-list-footer">
                <a href={this.props.contractApp.getAnnotationsListAnchor()}>See all Annotations</a>
            </div>
          </div>
        );
    },
    render: function() {
        var annotationsList = [];
        var self = this;
        if(this.props.annotationsCollection.models.length > 0) {
          if(this.props.annotationsCollection.sort_key === "category") {
            return this.sortByCluster();
          }
          return this.sortByPage();
        } else {
            return (
              <div className="annotations-list" id="id-annotations-list">
                {this.state.message}
              </div>
            );
        }
    }
});

var AnnotationsViewer = React.createClass({
    getInitialState: function() {
        return {
            scrollBtnStyle: 'display:none'
        }
    },
    handleGotoTop: function(e) {
        e.preventDefault();
        this.props.contractApp.trigger("annotations:scroll-to-top");
    },
    componentDidMount: function() {
        var offset = 150;
        var duration = 200;

        $("#annotations-box").scroll(function() {
                if ($(this).scrollTop() > offset) {
                    $('.back-to-top').fadeIn(duration);
                } else {
                    $('.back-to-top').fadeOut(duration);
                }
        });

        $('.back-to-top').click(function(event) {
            event.preventDefault();
            $('.annotation-inner-viewer').animate({scrollTop: 0}, duration);
            return false;
        })
    },
    render: function() {
        return(
            <div className="annotations-viewer" style={this.props.style}>
                <div className="annotation-inner-viewer" id="annotations-box">
                    <AnnotationHeader annotationsCollection={this.props.annotationsCollection} />
                    <AnnotationsSort
                        contractApp={this.props.contractApp}
                        annotationsCollection={this.props.annotationsCollection} />
                    <AnnotationsList
                        contractApp={this.props.contractApp}
                        annotationsCollection={this.props.annotationsCollection} />
                </div>
                <a href="#" className="back-to-top btn btn-primary"><i className="fa fa-arrow-up"></i></a>
           </div>
        );
    }
});