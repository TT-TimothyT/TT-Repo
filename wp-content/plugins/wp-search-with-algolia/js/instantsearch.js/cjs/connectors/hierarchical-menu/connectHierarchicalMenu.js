"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _utils = require("../../lib/utils");
var _excluded = ["name", "escapedValue", "data", "path"];
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }
function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
var withUsage = (0, _utils.createDocumentationMessageGenerator)({
  name: 'hierarchical-menu',
  connector: true
});
var DEFAULT_SORT = ['name:asc'];
/**
 * **HierarchicalMenu** connector provides the logic to build a custom widget
 * that will give the user the ability to explore facets in a tree-like structure.
 *
 * This is commonly used for multi-level categorization of products on e-commerce
 * websites. From a UX point of view, we suggest not displaying more than two
 * levels deep.
 *
 * @type {Connector}
 * @param {function(HierarchicalMenuRenderingOptions, boolean)} renderFn Rendering function for the custom **HierarchicalMenu** widget.
 * @param {function} unmountFn Unmount function called when the widget is disposed.
 * @return {function(CustomHierarchicalMenuWidgetParams)} Re-usable widget factory for a custom **HierarchicalMenu** widget.
 */
var connectHierarchicalMenu = function connectHierarchicalMenu(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : _utils.noop;
  (0, _utils.checkRendering)(renderFn, withUsage());
  return function (widgetParams) {
    var _ref = widgetParams || {},
      attributes = _ref.attributes,
      _ref$separator = _ref.separator,
      separator = _ref$separator === void 0 ? ' > ' : _ref$separator,
      _ref$rootPath = _ref.rootPath,
      rootPath = _ref$rootPath === void 0 ? null : _ref$rootPath,
      _ref$showParentLevel = _ref.showParentLevel,
      showParentLevel = _ref$showParentLevel === void 0 ? true : _ref$showParentLevel,
      _ref$limit = _ref.limit,
      limit = _ref$limit === void 0 ? 10 : _ref$limit,
      _ref$showMore = _ref.showMore,
      showMore = _ref$showMore === void 0 ? false : _ref$showMore,
      _ref$showMoreLimit = _ref.showMoreLimit,
      showMoreLimit = _ref$showMoreLimit === void 0 ? 20 : _ref$showMoreLimit,
      _ref$sortBy = _ref.sortBy,
      sortBy = _ref$sortBy === void 0 ? DEFAULT_SORT : _ref$sortBy,
      _ref$transformItems = _ref.transformItems,
      transformItems = _ref$transformItems === void 0 ? function (items) {
        return items;
      } : _ref$transformItems;
    if (!attributes || !Array.isArray(attributes) || attributes.length === 0) {
      throw new Error(withUsage('The `attributes` option expects an array of strings.'));
    }
    if (showMore === true && showMoreLimit <= limit) {
      throw new Error(withUsage('The `showMoreLimit` option must be greater than `limit`.'));
    }
    // we need to provide a hierarchicalFacet name for the search state
    // so that we can always map $hierarchicalFacetName => real attributes
    // we use the first attribute name
    var _attributes = _slicedToArray(attributes, 1),
      hierarchicalFacetName = _attributes[0];
    var sendEvent;

    // Provide the same function to the `renderFn` so that way the user
    // has to only bind it once when `isFirstRendering` for instance
    var toggleShowMore = function toggleShowMore() {};
    function cachedToggleShowMore() {
      toggleShowMore();
    }
    var _refine;
    var isShowingMore = false;
    function createToggleShowMore(renderOptions, widget) {
      return function () {
        isShowingMore = !isShowingMore;
        widget.render(renderOptions);
      };
    }
    function getLimit() {
      return isShowingMore ? showMoreLimit : limit;
    }
    function _prepareFacetValues(facetValues) {
      return facetValues.slice(0, getLimit()).map(function (_ref2) {
        var label = _ref2.name,
          value = _ref2.escapedValue,
          data = _ref2.data,
          path = _ref2.path,
          subValue = _objectWithoutProperties(_ref2, _excluded);
        var item = _objectSpread(_objectSpread({}, subValue), {}, {
          value: value,
          label: label,
          data: null
        });
        if (Array.isArray(data)) {
          item.data = _prepareFacetValues(data);
        }
        return item;
      });
    }
    return {
      $$type: 'ais.hierarchicalMenu',
      init: function init(initOptions) {
        var instantSearchInstance = initOptions.instantSearchInstance;
        renderFn(_objectSpread(_objectSpread({}, this.getWidgetRenderState(initOptions)), {}, {
          instantSearchInstance: instantSearchInstance
        }), true);
      },
      render: function render(renderOptions) {
        var instantSearchInstance = renderOptions.instantSearchInstance;
        toggleShowMore = createToggleShowMore(renderOptions, this);
        renderFn(_objectSpread(_objectSpread({}, this.getWidgetRenderState(renderOptions)), {}, {
          instantSearchInstance: instantSearchInstance
        }), false);
      },
      dispose: function dispose(_ref3) {
        var state = _ref3.state;
        unmountFn();
        return state.removeHierarchicalFacet(hierarchicalFacetName).setQueryParameter('maxValuesPerFacet', undefined);
      },
      getRenderState: function getRenderState(renderState, renderOptions) {
        return _objectSpread(_objectSpread({}, renderState), {}, {
          hierarchicalMenu: _objectSpread(_objectSpread({}, renderState.hierarchicalMenu), {}, _defineProperty({}, hierarchicalFacetName, this.getWidgetRenderState(renderOptions)))
        });
      },
      getWidgetRenderState: function getWidgetRenderState(_ref4) {
        var _this = this;
        var results = _ref4.results,
          state = _ref4.state,
          createURL = _ref4.createURL,
          instantSearchInstance = _ref4.instantSearchInstance,
          helper = _ref4.helper;
        var items = [];
        var canToggleShowMore = false;

        // Bind createURL to this specific attribute
        var _createURL = function _createURL(facetValue) {
          return createURL(function (uiState) {
            return _this.getWidgetUiState(uiState, {
              searchParameters: state.resetPage().toggleFacetRefinement(hierarchicalFacetName, facetValue),
              helper: helper
            });
          });
        };
        if (!sendEvent) {
          sendEvent = (0, _utils.createSendEventForFacet)({
            instantSearchInstance: instantSearchInstance,
            helper: helper,
            attribute: function attribute(facetValue) {
              var index = facetValue.split(separator).length - 1;
              return attributes[index];
            },
            widgetType: this.$$type
          });
        }
        if (!_refine) {
          _refine = function _refine(facetValue) {
            sendEvent('click:internal', facetValue);
            helper.toggleFacetRefinement(hierarchicalFacetName, facetValue).search();
          };
        }
        if (results) {
          var facetValues = results.getFacetValues(hierarchicalFacetName, {
            sortBy: sortBy,
            facetOrdering: sortBy === DEFAULT_SORT
          });
          var facetItems = facetValues && !Array.isArray(facetValues) && facetValues.data ? facetValues.data : [];

          // If the limit is the max number of facet retrieved it is impossible to know
          // if the facets are exhaustive. The only moment we are sure it is exhaustive
          // is when it is strictly under the number requested unless we know that another
          // widget has requested more values (maxValuesPerFacet > getLimit()).
          // Because this is used for making the search of facets unable or not, it is important
          // to be conservative here.
          var hasExhaustiveItems = (state.maxValuesPerFacet || 0) > getLimit() ? facetItems.length <= getLimit() : facetItems.length < getLimit();
          canToggleShowMore = showMore && (isShowingMore || !hasExhaustiveItems);
          items = transformItems(_prepareFacetValues(facetItems), {
            results: results
          });
        }
        return {
          items: items,
          refine: _refine,
          canRefine: items.length > 0,
          createURL: _createURL,
          sendEvent: sendEvent,
          widgetParams: widgetParams,
          isShowingMore: isShowingMore,
          toggleShowMore: cachedToggleShowMore,
          canToggleShowMore: canToggleShowMore
        };
      },
      getWidgetUiState: function getWidgetUiState(uiState, _ref5) {
        var searchParameters = _ref5.searchParameters;
        var path = searchParameters.getHierarchicalFacetBreadcrumb(hierarchicalFacetName);
        return removeEmptyRefinementsFromUiState(_objectSpread(_objectSpread({}, uiState), {}, {
          hierarchicalMenu: _objectSpread(_objectSpread({}, uiState.hierarchicalMenu), {}, _defineProperty({}, hierarchicalFacetName, path))
        }), hierarchicalFacetName);
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(searchParameters, _ref6) {
        var uiState = _ref6.uiState;
        var values = uiState.hierarchicalMenu && uiState.hierarchicalMenu[hierarchicalFacetName];
        if (searchParameters.isConjunctiveFacet(hierarchicalFacetName) || searchParameters.isDisjunctiveFacet(hierarchicalFacetName)) {
          process.env.NODE_ENV === 'development' ? (0, _utils.warning)(false, "HierarchicalMenu: Attribute \"".concat(hierarchicalFacetName, "\" is already used by another widget applying conjunctive or disjunctive faceting.\nAs this is not supported, please make sure to remove this other widget or this HierarchicalMenu widget will not work at all.")) : void 0;
          return searchParameters;
        }
        if (searchParameters.isHierarchicalFacet(hierarchicalFacetName)) {
          var facet = searchParameters.getHierarchicalFacetByName(hierarchicalFacetName);
          process.env.NODE_ENV === 'development' ? (0, _utils.warning)((0, _utils.isEqual)(facet.attributes, attributes) && facet.separator === separator && facet.rootPath === rootPath, 'Using Breadcrumb and HierarchicalMenu on the same facet with different options overrides the configuration of the HierarchicalMenu.') : void 0;
        }
        var withFacetConfiguration = searchParameters.removeHierarchicalFacet(hierarchicalFacetName).addHierarchicalFacet({
          name: hierarchicalFacetName,
          attributes: attributes,
          separator: separator,
          rootPath: rootPath,
          showParentLevel: showParentLevel
        });
        var currentMaxValuesPerFacet = withFacetConfiguration.maxValuesPerFacet || 0;
        var nextMaxValuesPerFacet = Math.max(currentMaxValuesPerFacet, showMore ? showMoreLimit : limit);
        var withMaxValuesPerFacet = withFacetConfiguration.setQueryParameter('maxValuesPerFacet', nextMaxValuesPerFacet);
        if (!values) {
          return withMaxValuesPerFacet.setQueryParameters({
            hierarchicalFacetsRefinements: _objectSpread(_objectSpread({}, withMaxValuesPerFacet.hierarchicalFacetsRefinements), {}, _defineProperty({}, hierarchicalFacetName, []))
          });
        }
        return withMaxValuesPerFacet.addHierarchicalFacetRefinement(hierarchicalFacetName, values.join(separator));
      }
    };
  };
};
function removeEmptyRefinementsFromUiState(indexUiState, attribute) {
  if (!indexUiState.hierarchicalMenu) {
    return indexUiState;
  }
  if (!indexUiState.hierarchicalMenu[attribute] || indexUiState.hierarchicalMenu[attribute].length === 0) {
    delete indexUiState.hierarchicalMenu[attribute];
  }
  if (Object.keys(indexUiState.hierarchicalMenu).length === 0) {
    delete indexUiState.hierarchicalMenu;
  }
  return indexUiState;
}
var _default = exports.default = connectHierarchicalMenu;