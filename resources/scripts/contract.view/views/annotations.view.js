var AnnotationHeader = React.createClass({
    componentDidMount: function () {
        var self = this;
        this.props.annotationsCollection.on("reset", function () {
            self.forceUpdate();
        });
    },
    render: function () {
        var count = this.props.annotationsCollection.totalAnnotations();
        return (
            <div className="annotation-title">{count} {LANG.annotations}</div>
        );
    }
});

var PageLink = React.createClass({
    getInitialState: function () {
        return {
            id: '',
            pageNo: '',
            annotationType: ''
        }
    },
    componentDidMount: function () {
        var self = this;
        this.setPageState();
        this.props.contractApp.on("annotations:highlight", function (annotation) {
            if (annotation.id === self.state.id) {
                if (self.state.annotationType === "pdf") {
                    location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                } else {
                    location.hash = "#/text/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                }
            }
        });
        this.props.contractApp.on("change:selected_annotation_id-1", function () {
            if (self.props.contractApp.getSelectedAnnotation() === self.state.id) {
                location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
            }
        });
    },
    setPageState: function () {
        var id = this.props.annotation.get('id');
        var pageNo = this.props.annotation.get('page_no') || this.props.annotation.get('page');
        var annotationType = "text";
        if (this.props.annotation.get('shapes')) {
            annotationType = "pdf";
        }

        this.setState({
            id: id,
            pageNo: pageNo,
            annotationType: annotationType
        });
    },
    handleAnnotationClick: function (e) {
        var self = this;
        e.preventDefault();
        switch (this.state.annotationType) {
            case "pdf":
                this.props.contractApp.trigger("annotations:highlight", {id: self.state.id});
                this.props.contractApp.setView("pdf");
                this.props.contractApp.setSelectedAnnotation(self.state.id);
                if (self.props.contractApp.getCurrentPage() == self.state.pageNo) {
                    var self = this;
                    setTimeout(function () {
                        self.props.contractApp.showPdfAnnotationPopup(self.state.id)
                    }, 300);
                }
                this.props.contractApp.setCurrentPage(self.state.pageNo);
                this.props.contractApp.triggerUpdatePdfPaginationPage(self.state.pageNo);
                break;
            case "text":
                this.props.contractApp.trigger("annotations:highlight", {id: self.state.id});
                var self = this;
                setTimeout(function () {
                    self.props.contractApp.showTextAnnotationPopup(self.state.id)
                }, 300);
                this.props.contractApp.setView("text");
                this.props.contractApp.setCurrentPage(self.state.pageNo);
                break;
        }
    },
    render: function () {
        if (this.props.article_reference != '') {
            return (
                <span className="page-gap">
                   <a href="#" onClick={this.handleAnnotationClick}>{this.props.article_reference}</a>
                    {this.props.last ? ', ' : ''}
               </span>
            )
        } else {
            return (
                <span className="page-gap">
                   <a href="#" onClick={this.handleAnnotationClick}>{this.props.page}</a>
                    {this.props.last ? ', ' : ''}
               </span>
            )
        }
    }
});

var AnnotationItem = React.createClass({
    getInitialState: function () {
        return {
            maxWords: 10,
            id: '',
            annotation_id: '',
            text: '',
            shortText: '',
            showEllipse: '',
            showMoreFlag: '',
            highlight: false,
            annotationList: []
        }
    },
    getCategory: function () {
        return this.state.annotationList[0].get('category');
    },
    shallShowEllipse: function (text) {
        var words = (text + "").split(' ');
        if (words.length >= this.state.maxWords) {
            return true;
        }
        return false;
    },
    truncate: function (text) {
        var words = (text + "").split(" ");
        words = words.splice(0, this.state.maxWords);
        return words.join(" ");
    },
    setAnnotationState: function () {
        var firstAnnotation = this.state.annotationList[0];
        var text = firstAnnotation.get('text') ? firstAnnotation.get('text').trim() : '';
        var id = firstAnnotation.get('id');
        var annotation_id = firstAnnotation.get('annotation_id');
        var showEllipse = this.shallShowEllipse(text);
        var shortText = "";
        if (showEllipse) {
            shortText = this.truncate(text);
        }
        var ann = this.props.annotationsCollection.get(this.props.contractApp.getSelectedAnnotation());
        var showMoreFlag = (ann && ann.get('annotation_id') === annotation_id) ? true : false;

        this.setState({
            id: id,
            text: text,
            annotation_id: annotation_id,
            shortText: shortText.trim(),
            showEllipse: showEllipse,
            showMoreFlag: showMoreFlag,
            highlight: showMoreFlag
        });
    },
    getPages: function () {
        var self = this;
        this.props.annotation.sort(function (a, b) {
            return a.get('page') - b.get('page');
        });

        var annotationGroupByPage = _.groupBy(this.props.annotation, function (a) {
            return a.get('page');
        });

        annotationGroupByPage = _.toArray(annotationGroupByPage);

        var length = annotationGroupByPage.length;

        return annotationGroupByPage.map(function (annotation, index) {
            var page = annotation[0].get('page');
            var last = false;
            if (index < (length - 1)) {
                last = true;
            }
            var count = annotation.length;
            var ref = annotation.map(function (annotation, index) {
                var l = false;
                if (index < (count - 1)) {
                    l = true;
                }
                var article_reference = (annotation.get('article_reference') != '') ? annotation.get('article_reference') : '';
                return (<PageLink key={index} contractApp={self.props.contractApp} annotation={annotation} last={l} page={page}
                                  article_reference={article_reference}/>)
            });

            return (
                <span>
                   {LANG.page} {page} ({ref}){last ? ', ' : ''}
                 </span>
            );
        });
    },
    componentWillMount: function () {
        this.setState({annotationList: this.props.annotation});
    },
    componentDidMount: function () {
        this.setAnnotationState();
        var self = this;
        this.props.contractApp.on("annotations:highlight", function (annotation) {
            debug('annotations:highlight');
            var current = annotation.id;

            var highlight = false;
            self.state.annotationList.map(function (annotation, index) {
                if (current == annotation.get('id')) {
                    highlight = true;
                }
            });

            if (highlight) {
                self.setState({
                    showMoreFlag: true,
                    highlight: true
                });
            } else {
                self.setState({
                    showMoreFlag: false,
                    highlight: false
                });
            }
        });
    },
    handleEllipsis: function (e) {
        e.preventDefault();
        this.setState({showMoreFlag: !this.state.showMoreFlag});
    },
    getShowText: function () {
        var ellipsistext = "";
        var firstAnnotation = this.props.annotation[0];
        var showText = firstAnnotation.get('text') ? firstAnnotation.get('text').trim() : '';
        if (this.state.showEllipse) {
            showText = this.state.text + ' ';
            ellipsistext = LANG.less;
            if (!this.state.showMoreFlag) {
                ellipsistext = LANG.more;
                showText = this.state.shortText + '... ';
            }
        }

        if (this.state.text != '') {
            showText = (<span className="annotation-item-content">
                <span dangerouslySetInnerHTML={{__html: nl2br(showText)}}></span>
                <nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis}
                         dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>);
        }
        else {
            showText = '';
        }
        return showText;
    },
    getPageClasses: function () {
        var className = "";

        this.props.annotation.map(function (annotation, index) {
            className += ' p-' + annotation.get('id');
        });

        return className;
    },
    render: function () {
        var currentAnnotationClass = (this.state.highlight) ? "annotation-item selected-annotation" : "annotation-item";

        var category = this.getCategory();
        return (
            <div className={currentAnnotationClass + this.getPageClasses()} id={this.state.annotation_id}>
                <p className="annotation-category">{category}</p>
                <p className="annotation-text">{this.getShowText()}</p>
                <div className="annotation-page">{this.getPages()}</div>
            </div>
        )
    }
});

var AnnotationsSort = React.createClass({
    getInitialState: function () {
        return {
            show: false,
            sortBy: "category"
        }
    },
    componentDidMount: function () {
        var self = this;
        this.props.annotationsCollection.on("reset", function () {
            if (self.props.annotationsCollection.models.length > 0) {
                self.setState({show: true});
            }
        });
        this.setState({sortBy: "category"});
    },
    onClickPage: function (e) {
        e.preventDefault();
        this.props.annotationsCollection.setSortByKey("page");
        this.props.contractApp.resetSelectedAnnotation();
        this.props.contractApp.trigger("annotations:render");
        this.setState({sortBy: "page"});
    },
    onClickTopic: function (e) {
        e.preventDefault();
        this.props.annotationsCollection.setSortByKey("category");
        this.props.contractApp.resetSelectedAnnotation();
        this.props.contractApp.trigger("annotations:render");
        this.setState({sortBy: "category"});
    },
    render: function () {
        var pageClassName = "active", topicClassName = "";
        if (this.state.sortBy == "category") {
            pageClassName = "";
            topicClassName = "active";
        }
        var activeClass = this.state.sortBy;
        if (this.state.show) {
            return (
                <div className="annotation-sort">
                    <span className={pageClassName} onClick={this.onClickPage}>{LANG.by_page}</span>
                    <span className={topicClassName} onClick={this.onClickTopic}>{LANG.by_category}</span>
                </div>
            );
        } else {
            return (<div></div>);
        }
    }
});

var AnnotationsList = React.createClass({
    getInitialState: function () {
        return {
            message: LANG.annotation_loading
        }
    },
    componentDidMount: function () {
        var self = this;
        this.props.annotationsCollection.on("reset", function () {
            if (self.props.annotationsCollection.totalAnnotations() > 0) {
                self.setState({message: ""});
            } else {
                self.setState({message: LANG.annotation_not_found});
            }
            if (self.props.contractApp.getSelectedAnnotation()) {
                self.props.contractApp.trigger("annotations:scroll-to-selected-annotation");
            }
        });

        this.props.contractApp.on("annotations:render", function (sortBy) {
            self.forceUpdate();
        });
        this.props.contractApp.on("annotations:highlight", function (annotation) {
            var that = self;
            var annotation_model = self.props.annotationsCollection.get(annotation.id);
            setTimeout(function () {
                that.scrollToAnnotation(annotation_model.get('id'));
            }, 100);
        });
        this.props.contractApp.on("annotations:scroll-to-selected-annotation", function () {
            var annotation = self.props.annotationsCollection.get(self.props.contractApp.getSelectedAnnotation());
            if (annotation) {
                self.scrollToAnnotation(annotation.get('id'));
            }
        });
        this.props.contractApp.on("annotations:scroll-to-top", function () {
            self.scrollToTop();
        });
        this.props.contractApp.on("annotations:scroll-to-cluster", function (cluster) {
            self.scrollToCluster(cluster);
        });
    },
    scrollToCluster: function (cluster) {
        if ($('#' + cluster).offset()) {
            var pageOffsetTop = $('#' + cluster).offset().top;
            var parentTop = $('.annotations-viewer').scrollTop();
            var parentOffsetTop = $('.annotations-viewer').offset().top;
            $('.annotations-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop}, 200);
        }
    },
    scrollToAnnotation: function (annotation_id) {
        if (annotation_id) {
            var pageOffsetTop = $('.p-' + annotation_id).offset().top;
            var parentTop = $('.annotations-viewer').scrollTop();
            var parentOffsetTop = $('.annotations-viewer').offset().top;
            $('.annotations-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop}, 200);
            this.props.contractApp.resetSelectedAnnotation();
        }
    },
    scrollToTop: function (e) {
        e.preventDefault();
        $('.annotations-viewer').animate({scrollTop: 0}, 500);
    },
    getAnnotationItemsComponent: function (annotationsCollectionForList, showClusterAnyway) {
        var annotationsList = [];
        if (_.size(annotationsCollectionForList) > 0) {
            var i = 0;
            for (var annotation_id in annotationsCollectionForList) {
                annotationsList.push((<AnnotationItem
                    showClusterAnyway={showClusterAnyway}
                    key={i}
                    contractApp={this.props.contractApp}
                    annotationsCollection={this.props.annotationsCollection}
                    annotation={annotationsCollectionForList[annotation_id]}/>));
                i++;
            }
        }
        return annotationsList;
    },
    getAnnotationItemsComponentByPage: function (annotationsCollectionForList, showClusterAnyway) {
        var annotationsList = [];
        if (_.size(annotationsCollectionForList) > 0) {
            var i = 0;
            for (var page in annotationsCollectionForList) {
                for (var key in annotationsCollectionForList[page]) {
                    annotationsList.push((<AnnotationItem
                        showClusterAnyway={showClusterAnyway}
                        key={i}
                        contractApp={this.props.contractApp}
                        annotationsCollection={this.props.annotationsCollection}
                        annotation={[annotationsCollectionForList[page][key]]}/>));
                    i++;
                }

            }
        }
        return annotationsList;

    },
    sortByPage: function () {
        if (this.props.annotationsCollection.models.length > 0) {
            this.props.annotationsCollection.sort();
            return (
                <div className="annotations-list" id="id-annotations-list">
                    {this.getAnnotationItemsComponentByPage(this.props.annotationsCollection.groupByPage(), true)}
                </div>
            );
        }
        return [];
    },
    sortByCategory: function () {
        if (this.props.annotationsCollection.models.length > 0) {
            this.props.annotationsCollection.sort();
            return (
                <div className="annotations-list" id="id-annotations-list">
                    {this.getAnnotationItemsComponent(this.props.annotationsCollection.groupByCategory(), true)}
                    <AnnotationsCategoryList
                        contractApp={this.props.contractApp}
                        annotationsCollection={this.props.annotationsCollection}/>
                </div>
            );
        }
    },
    render: function () {
        if (this.props.annotationsCollection.models.length > 0) {
            if (this.props.annotationsCollection.sort_key === "category") {
                return this.sortByCategory();
            }
            return this.sortByPage();
        } else {
            return (
                <div className="annotations-list" id="id-annotations-list">
                    <p className="annotation-loading">{this.state.message}</p>
                </div>
            );
        }
    }
});

var AnnotationsCategoryList = React.createClass({
    isHeaderCategory: function (categoryKey) {
        var headerNumber = categoryKey.substr(0, categoryKey.indexOf("-"));
        if (["i", "ii", "iii", "1", "2", "3", "4", "5", "6"].indexOf(headerNumber) !== -1) {
            return true;
        }
        return false;
    },
    isUnusedCategory: function (categoryKey) {
        if (this.usedCategories.indexOf(categoryKey) === -1) {
            return true;
        }
        return false;
    },
    getCategoryName: function (categoryModel) {
        return categoryModel.get("name");
    },
    render: function () {
        var allCategories = this.props.contractApp.getAnnotationCategories().models;
        this.usedCategories = this.props.annotationsCollection.pluck("category_key");
        var unusedCategoriesDom = [];
        for (var i = 0; i < allCategories.length; i++) {
            if (this.isHeaderCategory(allCategories[i].get("key"))) {
                unusedCategoriesDom.push(<div key={i}><b>{this.getCategoryName(allCategories[i])}</b></div>);
            }
            else if (this.isUnusedCategory(allCategories[i].get("key")) && this.getCategoryName(allCategories[i])) {
                unusedCategoriesDom.push(<span key={i}>{this.getCategoryName(allCategories[i])}</span>);
            }
        }
        return (
            <div className="unused-categories">
                <span className="unused-categories-desc">{LANG.unsed_category_desc}</span>
                {unusedCategoriesDom}
            </div>
        );
    }
});
var AnnotationsViewer = React.createClass({
    componentDidMount: function () {
        var self = this;

        this.props.contractApp.on("annotationCreated", function (annotation) {
            self.props.annotationsCollection.fetch({reset: true});
            self.forceUpdate();
        });
        this.props.contractApp.on("annotationUpdated", function (annotation) {
            self.props.annotationsCollection.fetch({reset: true});
            self.forceUpdate();
        });
        this.props.contractApp.on("annotationDeleted", function (annotation) {
            self.props.annotationsCollection.remove(annotation);
            self.forceUpdate();
        });
    },
    render: function () {
        return (
            <div className="annotations-viewer" style={this.props.style}>
                <AnnotationHeader contractApp={this.props.contractApp}
                                  annotationsCollection={this.props.annotationsCollection}/>
                <AnnotationsSort
                    contractApp={this.props.contractApp}
                    annotationsCollection={this.props.annotationsCollection}/>
                <AnnotationsList
                    contractApp={this.props.contractApp}
                    annotationsCollection={this.props.annotationsCollection}/>
            </div>
        );
    }
});