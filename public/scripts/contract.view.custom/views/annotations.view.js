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
    setAnnotationState: function() {
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
        var category_key = this.props.annotation.get('category_key');
        var category = this.props.annotation.get('category');
        var categoryEn = category.split("//")[0];
        var categoryFr = (category.split("//")[1])?category.split("//")[1]:"";
        var id = this.props.annotation.get('id');
        var text = getText(this.props.annotation);
        var showEllipse = shallShowEllipse(text);
        var pageNo = this.props.annotation.get('page_no') || this.props.annotation.get('page');
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
            text: text.trim(),
            cluster: cluster,
            shortText: shortText.trim(),
            category_key: category_key,
            categoryEn: categoryEn.trim(),
            categoryFr: categoryFr.trim(),
            pageNo: pageNo,
            annotationType: annotationType,
            showEllipse: showEllipse,
            highlight: highlight,
            showMoreFlag: showMoreFlag
        });
    },
    componentDidMount: function() {
        var self = this;
        this.setAnnotationState();
        this.props.contractApp.on("annotations:highlight", function(annotation) {
            if(annotation.id === self.state.id) {
                self.setState({
                    showMoreFlag: true,
                    highlight: true
                });
                if(self.state.annotationType === "pdf") {
                    location.hash = "#/pdf/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                } else {
                    location.hash = "#/text/page/" + self.state.pageNo + "/annotation/" + self.state.id;
                }
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
        this.props.annotation.on("add", function() {
            self.setAnnotationState();
        });
        this.props.annotation.on("change", function() {
            self.setAnnotationState();
        });
    },
    handleAnnotationClick: function(e) {
        var self = this;
        e.preventDefault();
        switch(this.state.annotationType) {
            case "pdf":
                this.props.contractApp.setView("pdf");
                this.props.contractApp.setSelectedAnnotation(self.state.id);
                this.props.contractApp.trigger("annotations:highlight", {id: self.state.id});
                this.props.contractApp.setCurrentPage(self.state.pageNo);
                this.props.contractApp.triggerUpdatePdfPaginationPage(self.state.pageNo);
                break
            case "text":
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
        if (this.props.prevAnnotation === undefined) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="link annotation-category-en"><a href="#" onClick={this.handleAnnotationClick}>{this.state.categoryEn}</a></span>
                    <span className="link annotation-category-fr" onClick={this.handleAnnotationClick}>{this.state.categoryFr}</span>
                    <span className="annotation-item-content" >{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page: {this.state.pageNo}</span>
                </div>
            );
        } else if (this.props.annotation.attributes.category_key !== this.props.prevAnnotation.attributes.category_key) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="link annotation-category-en"><a href="#" onClick={this.handleAnnotationClick}>{this.state.categoryEn}</a></span>
                    <span className="link annotation-category-fr" onClick={this.handleAnnotationClick}>{this.state.categoryFr}</span>
                    <span className="annotation-item-content" >{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page: {this.state.pageNo}</span>
                </div>
            );
        } else if (this.props.annotation.attributes.text !== this.props.prevAnnotation.attributes.text) {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="annotation-item-content" >{showText}<nobr><a className="annotation-item-ellipsis" href="#" onClick={this.handleEllipsis} dangerouslySetInnerHTML={{__html: ellipsistext}}></a></nobr></span>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page: {this.state.pageNo}</span>
                </div>
            );
        } else {
            return (
                <div className={currentAnnotationClass} id={this.state.id}>
                    <span className="link annotation-item-page" onClick={this.handleAnnotationClick}>Page: {this.state.pageNo}</span>
                </div>
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
            sortBy: "category"
        }
    },
    componentDidMount: function() {
        var self = this;
        this.props.annotationsCollection.on("reset", function() {
            if(self.props.annotationsCollection.models.length > 0) {
                self.setState({show: true});
            }
        });
        this.setState({sortBy: "category"});
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
        this.setState({sortBy: "category"});
    },
    render: function() {
        var pageClassName = "active", topicClassName = "";
        if(this.state.sortBy == "category") {
            pageClassName = "";
            topicClassName = "active";
        }
        var activeClass = this.state.sortBy;
        if(this.state.show) {
            return (
                <div className="annotation-sort">
                    <span className={pageClassName} onClick={this.onClickPage}>By Page</span>
                    <span className={topicClassName} onClick={this.onClickTopic}>By Category</span>
                </div>
            );
        } else {
            return (<div></div>);
        }
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
        this.props.contractApp.on("annotationCreated", function(annotation) {
            self.props.annotationsCollection.fetch({reset: true});
            self.forceUpdate();
        });
        this.props.contractApp.on("annotationUpdated", function(annotation) {
            self.props.annotationsCollection.add(annotation, {
                merge: true
            });            
            self.forceUpdate();
        });
        this.props.contractApp.on("annotationDeleted", function(annotation) {
            self.props.annotationsCollection.remove(annotation);
            self.forceUpdate();
        });
    },
    scrollToCluster: function(cluster) {
        if($('#'+cluster).offset()) {
            var pageOffsetTop = $('#'+cluster).offset().top;
            var parentTop = $('.annotations-viewer').scrollTop();
            var parentOffsetTop = $('.annotations-viewer').offset().top
            $('.annotations-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop},200);            
        }
    },
    scrollToAnnotation: function(annotation_id) {
        if(annotation_id) {
            var pageOffsetTop = $('#'+annotation_id).offset().top;
            var parentTop = $('.annotations-viewer').scrollTop();
            var parentOffsetTop = $('.annotations-viewer').offset().top
            $('.annotations-viewer').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop},200);
            this.props.contractApp.resetSelectedAnnotation();
        }
    },
    scrollToTop: function(e) {
        e.preventDefault();
        $('.annotations-viewer').animate({scrollTop: 0}, 500);
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
                    <a onClick={this.scrollToTop} href="#">Go to Top</a>
                    <a href={this.props.contractApp.getAnnotationsListAnchor()}>See all Annotations</a>
                </div>          
              </div>
            );
        }
        return [];
    },
    sortByCategory: function() {
        if(this.props.annotationsCollection.models.length > 0) {
            this.props.annotationsCollection.sort();
            return (
              <div className="annotations-list" id="id-annotations-list">
                {this.getAnnotationItemsComponent(this.props.annotationsCollection, true)}
                <div className="annotations-list-footer">
                    <a onClick={this.scrollToTop} href="#">Go to Top</a>
                    <a href={this.props.contractApp.getAnnotationsListAnchor()}>See all Annotations</a>
                </div>          
              </div>
            );
        }
    },    
    render: function() {
        var annotationsList = [];
        var self = this;
        if(this.props.annotationsCollection.models.length > 0) {
          if(this.props.annotationsCollection.sort_key === "category") {
            return this.sortByCategory();
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
    handleGotoTop: function(e) {
        e.preventDefault();
        this.props.contractApp.trigger("annotations:scroll-to-top");
    },
    render: function() {
        return(
            <div className="annotations-viewer" style={this.props.style}>
                <AnnotationHeader annotationsCollection={this.props.annotationsCollection} />
                <AnnotationsSort
                    contractApp={this.props.contractApp} 
                    annotationsCollection={this.props.annotationsCollection} />
                <AnnotationsList 
                    contractApp={this.props.contractApp} 
                    annotationsCollection={this.props.annotationsCollection} />
            </div>
        );
    }
});