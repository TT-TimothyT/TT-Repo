"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.EXPERIMENTAL_connectDynamicWidgets = exports.EXPERIMENTAL_connectConfigureRelatedItems = exports.EXPERIMENTAL_connectAnswers = void 0;
Object.defineProperty(exports, "connectAutocomplete", {
  enumerable: true,
  get: function get() {
    return _connectAutocomplete.default;
  }
});
Object.defineProperty(exports, "connectBreadcrumb", {
  enumerable: true,
  get: function get() {
    return _connectBreadcrumb.default;
  }
});
Object.defineProperty(exports, "connectClearRefinements", {
  enumerable: true,
  get: function get() {
    return _connectClearRefinements.default;
  }
});
Object.defineProperty(exports, "connectConfigure", {
  enumerable: true,
  get: function get() {
    return _connectConfigure.default;
  }
});
Object.defineProperty(exports, "connectCurrentRefinements", {
  enumerable: true,
  get: function get() {
    return _connectCurrentRefinements.default;
  }
});
Object.defineProperty(exports, "connectDynamicWidgets", {
  enumerable: true,
  get: function get() {
    return _connectDynamicWidgets.default;
  }
});
Object.defineProperty(exports, "connectFrequentlyBoughtTogether", {
  enumerable: true,
  get: function get() {
    return _connectFrequentlyBoughtTogether.default;
  }
});
Object.defineProperty(exports, "connectGeoSearch", {
  enumerable: true,
  get: function get() {
    return _connectGeoSearch.default;
  }
});
Object.defineProperty(exports, "connectHierarchicalMenu", {
  enumerable: true,
  get: function get() {
    return _connectHierarchicalMenu.default;
  }
});
Object.defineProperty(exports, "connectHits", {
  enumerable: true,
  get: function get() {
    return _connectHits.default;
  }
});
Object.defineProperty(exports, "connectHitsPerPage", {
  enumerable: true,
  get: function get() {
    return _connectHitsPerPage.default;
  }
});
Object.defineProperty(exports, "connectHitsWithInsights", {
  enumerable: true,
  get: function get() {
    return _connectHitsWithInsights.default;
  }
});
Object.defineProperty(exports, "connectInfiniteHits", {
  enumerable: true,
  get: function get() {
    return _connectInfiniteHits.default;
  }
});
Object.defineProperty(exports, "connectInfiniteHitsWithInsights", {
  enumerable: true,
  get: function get() {
    return _connectInfiniteHitsWithInsights.default;
  }
});
Object.defineProperty(exports, "connectLookingSimilar", {
  enumerable: true,
  get: function get() {
    return _connectLookingSimilar.default;
  }
});
Object.defineProperty(exports, "connectMenu", {
  enumerable: true,
  get: function get() {
    return _connectMenu.default;
  }
});
Object.defineProperty(exports, "connectNumericMenu", {
  enumerable: true,
  get: function get() {
    return _connectNumericMenu.default;
  }
});
Object.defineProperty(exports, "connectPagination", {
  enumerable: true,
  get: function get() {
    return _connectPagination.default;
  }
});
Object.defineProperty(exports, "connectPoweredBy", {
  enumerable: true,
  get: function get() {
    return _connectPoweredBy.default;
  }
});
Object.defineProperty(exports, "connectQueryRules", {
  enumerable: true,
  get: function get() {
    return _connectQueryRules.default;
  }
});
Object.defineProperty(exports, "connectRange", {
  enumerable: true,
  get: function get() {
    return _connectRange.default;
  }
});
Object.defineProperty(exports, "connectRatingMenu", {
  enumerable: true,
  get: function get() {
    return _connectRatingMenu.default;
  }
});
Object.defineProperty(exports, "connectRefinementList", {
  enumerable: true,
  get: function get() {
    return _connectRefinementList.default;
  }
});
Object.defineProperty(exports, "connectRelatedProducts", {
  enumerable: true,
  get: function get() {
    return _connectRelatedProducts.default;
  }
});
Object.defineProperty(exports, "connectRelevantSort", {
  enumerable: true,
  get: function get() {
    return _connectRelevantSort.default;
  }
});
Object.defineProperty(exports, "connectSearchBox", {
  enumerable: true,
  get: function get() {
    return _connectSearchBox.default;
  }
});
Object.defineProperty(exports, "connectSortBy", {
  enumerable: true,
  get: function get() {
    return _connectSortBy.default;
  }
});
Object.defineProperty(exports, "connectStats", {
  enumerable: true,
  get: function get() {
    return _connectStats.default;
  }
});
Object.defineProperty(exports, "connectToggleRefinement", {
  enumerable: true,
  get: function get() {
    return _connectToggleRefinement.default;
  }
});
Object.defineProperty(exports, "connectTrendingItems", {
  enumerable: true,
  get: function get() {
    return _connectTrendingItems.default;
  }
});
Object.defineProperty(exports, "connectVoiceSearch", {
  enumerable: true,
  get: function get() {
    return _connectVoiceSearch.default;
  }
});
var _utils = require("../lib/utils");
var _connectAnswers = _interopRequireDefault(require("./answers/connectAnswers"));
var _connectConfigureRelatedItems = _interopRequireDefault(require("./configure-related-items/connectConfigureRelatedItems"));
var _connectDynamicWidgets = _interopRequireDefault(require("./dynamic-widgets/connectDynamicWidgets"));
var _connectClearRefinements = _interopRequireDefault(require("./clear-refinements/connectClearRefinements"));
var _connectCurrentRefinements = _interopRequireDefault(require("./current-refinements/connectCurrentRefinements"));
var _connectHierarchicalMenu = _interopRequireDefault(require("./hierarchical-menu/connectHierarchicalMenu"));
var _connectHits = _interopRequireDefault(require("./hits/connectHits"));
var _connectHitsWithInsights = _interopRequireDefault(require("./hits/connectHitsWithInsights"));
var _connectHitsPerPage = _interopRequireDefault(require("./hits-per-page/connectHitsPerPage"));
var _connectInfiniteHits = _interopRequireDefault(require("./infinite-hits/connectInfiniteHits"));
var _connectInfiniteHitsWithInsights = _interopRequireDefault(require("./infinite-hits/connectInfiniteHitsWithInsights"));
var _connectMenu = _interopRequireDefault(require("./menu/connectMenu"));
var _connectNumericMenu = _interopRequireDefault(require("./numeric-menu/connectNumericMenu"));
var _connectPagination = _interopRequireDefault(require("./pagination/connectPagination"));
var _connectRange = _interopRequireDefault(require("./range/connectRange"));
var _connectRefinementList = _interopRequireDefault(require("./refinement-list/connectRefinementList"));
var _connectRelatedProducts = _interopRequireDefault(require("./related-products/connectRelatedProducts"));
var _connectSearchBox = _interopRequireDefault(require("./search-box/connectSearchBox"));
var _connectSortBy = _interopRequireDefault(require("./sort-by/connectSortBy"));
var _connectRatingMenu = _interopRequireDefault(require("./rating-menu/connectRatingMenu"));
var _connectStats = _interopRequireDefault(require("./stats/connectStats"));
var _connectToggleRefinement = _interopRequireDefault(require("./toggle-refinement/connectToggleRefinement"));
var _connectTrendingItems = _interopRequireDefault(require("./trending-items/connectTrendingItems"));
var _connectBreadcrumb = _interopRequireDefault(require("./breadcrumb/connectBreadcrumb"));
var _connectGeoSearch = _interopRequireDefault(require("./geo-search/connectGeoSearch"));
var _connectPoweredBy = _interopRequireDefault(require("./powered-by/connectPoweredBy"));
var _connectConfigure = _interopRequireDefault(require("./configure/connectConfigure"));
var _connectAutocomplete = _interopRequireDefault(require("./autocomplete/connectAutocomplete"));
var _connectQueryRules = _interopRequireDefault(require("./query-rules/connectQueryRules"));
var _connectVoiceSearch = _interopRequireDefault(require("./voice-search/connectVoiceSearch"));
var _connectRelevantSort = _interopRequireDefault(require("./relevant-sort/connectRelevantSort"));
var _connectFrequentlyBoughtTogether = _interopRequireDefault(require("./frequently-bought-together/connectFrequentlyBoughtTogether"));
var _connectLookingSimilar = _interopRequireDefault(require("./looking-similar/connectLookingSimilar"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
/** @deprecated answers is no longer supported */
var EXPERIMENTAL_connectAnswers = exports.EXPERIMENTAL_connectAnswers = (0, _utils.deprecate)(_connectAnswers.default, 'answers is no longer supported');

/** @deprecated use connectRelatedItems instead */
var EXPERIMENTAL_connectConfigureRelatedItems = exports.EXPERIMENTAL_connectConfigureRelatedItems = (0, _utils.deprecate)(_connectConfigureRelatedItems.default, 'EXPERIMENTAL_connectConfigureRelatedItems is deprecated and will be removed in a next minor version of InstantSearch. Please use connectRelatedItems instead.');

/** @deprecated use connectDynamicWidgets */
var EXPERIMENTAL_connectDynamicWidgets = exports.EXPERIMENTAL_connectDynamicWidgets = (0, _utils.deprecate)(_connectDynamicWidgets.default, 'use connectDynamicWidgets');