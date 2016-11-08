  var TestContractApp = ContractApp.extend({
    getMetadataSummaryLink: function() {
      return app_url + "/contract/" + this.getContractId();
    },
    getMetadataUrl: function() {
      return "/data/test/metadata.1034.json?1&1";
      // return "http://rc-elasticsearch.elasticbeanstalk.com/api/contract/" + this.getContractId() + "/metadata";  
    },  
    getAllPageUrl: function() {
      return "/data/test/text.733.json";
      // return "http://rc-elasticsearch.elasticbeanstalk.com/api/contract/" + this.getContractId() + "/text";
    },
    getAllAnnotationsUrl: function() {
      return "/data/test/allannotations.1034.json";
      // return "http://rc-elasticsearch.elasticbeanstalk.com/api/contract/" + this.getContractId() + "/annotations";
    },
    getSearchUrl: function() {
      return "http://rc-elasticsearch.elasticbeanstalk.com/api/contract/" + this.getContractId() + "/searchtext"
    },
    getPdfUrl: function() {
      return "/data/1/pages/1.pdf";      
      // var page_no = parseInt(this.getCurrentPage());
      // var pageModel = pagesCollection.where({ page_no: page_no});
      // if(pageModel && pageModel[0] && pageModel[0].attributes) {
      //     return pageModel[0].get("pdf_url");
      // }
      // return "";
    },
    getFullPdfUrl: function() {
      return "";
    },
    getLoadAnnotationsUrl: function() {
      return "/data/test";
      // return "http://rc-elasticsearch.elasticbeanstalk.com/api/contract/" + this.getContractId() + "/annotations"; 
    }
  });