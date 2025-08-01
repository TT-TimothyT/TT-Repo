var _excluded = ["root"];
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _extends() { _extends = Object.assign ? Object.assign.bind() : function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }
function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }
function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : String(i); }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
import { cx } from 'instantsearch-ui-components';
import { h, createRef, Component } from 'preact';
import { isSpecialClick, isEqual } from "../../lib/utils/index.js";
import SearchBox from "../SearchBox/SearchBox.js";
import Template from "../Template/Template.js";
import RefinementListItem from "./RefinementListItem.js";

// CSS types

var defaultProps = {
  cssClasses: {},
  depth: 0
};
function isHierarchicalMenuItem(facetValue) {
  return facetValue.data !== undefined;
}
var RefinementList = /*#__PURE__*/function (_Component) {
  _inherits(RefinementList, _Component);
  var _super = _createSuper(RefinementList);
  function RefinementList() {
    var _this;
    _classCallCheck(this, RefinementList);
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    _this = _super.call.apply(_super, [this].concat(args));
    _defineProperty(_assertThisInitialized(_this), "listRef", createRef());
    _defineProperty(_assertThisInitialized(_this), "searchBox", createRef());
    _defineProperty(_assertThisInitialized(_this), "lastRefinedValue", undefined);
    _defineProperty(_assertThisInitialized(_this), "_generateFacetItem", function (facetValue) {
      var subItems;
      if (isHierarchicalMenuItem(facetValue) && Array.isArray(facetValue.data) && facetValue.data.length > 0) {
        var _this$props$cssClasse = _this.props.cssClasses,
          root = _this$props$cssClasse.root,
          cssClasses = _objectWithoutProperties(_this$props$cssClasse, _excluded);
        subItems = h(RefinementList, _extends({}, _this.props, {
          // We want to keep `root` required for external usage but not for the
          // sub items.
          cssClasses: cssClasses,
          depth: _this.props.depth + 1,
          facetValues: facetValue.data,
          showMore: false,
          className: _this.props.cssClasses.childList
        }));
      }
      var url = _this.props.createURL(facetValue.value);
      var templateData = _objectSpread(_objectSpread({}, facetValue), {}, {
        url: url,
        attribute: _this.props.attribute,
        cssClasses: _this.props.cssClasses,
        isFromSearch: _this.props.isFromSearch
      });
      var key = facetValue.value;
      if (facetValue.isRefined !== undefined) {
        key += "/".concat(facetValue.isRefined);
      }
      if (facetValue.count !== undefined) {
        key += "/".concat(facetValue.count);
      }
      var refinementListItemClassName = cx(_this.props.cssClasses.item, facetValue.isRefined && _this.props.cssClasses.selectedItem, !facetValue.count && _this.props.cssClasses.disabledItem, Boolean(isHierarchicalMenuItem(facetValue) && Array.isArray(facetValue.data) && facetValue.data.length > 0) && _this.props.cssClasses.parentItem);
      return h(RefinementListItem, {
        templateKey: "item",
        key: key,
        facetValueToRefine: facetValue.value,
        handleClick: _this.handleItemClick,
        isRefined: facetValue.isRefined,
        className: refinementListItemClassName,
        subItems: subItems,
        templateData: templateData,
        templateProps: _this.props.templateProps
      });
    });
    // Click events on DOM tree like LABEL > INPUT will result in two click events
    // instead of one.
    // No matter the framework, see https://www.google.com/search?q=click+label+twice
    //
    // Thus making it hard to distinguish activation from deactivation because both click events
    // are very close. Debounce is a solution but hacky.
    //
    // So the code here checks if the click was done on or in a LABEL. If this LABEL
    // has a checkbox inside, we ignore the first click event because we will get another one.
    //
    // We also check if the click was done inside a link and then e.preventDefault() because we already
    // handle the url
    //
    // Finally, we always stop propagation of the event to avoid multiple levels RefinementLists to fail: click
    // on child would click on parent also
    _defineProperty(_assertThisInitialized(_this), "handleItemClick", function (_ref) {
      var facetValueToRefine = _ref.facetValueToRefine,
        isRefined = _ref.isRefined,
        originalEvent = _ref.originalEvent;
      if (isSpecialClick(originalEvent)) {
        // do not alter the default browser behavior
        // if one special key is down
        return;
      }
      var parent = originalEvent.target;
      if (parent === null || parent.parentNode === null) {
        return;
      }
      if (isRefined && parent.parentNode.querySelector('input[type="radio"]:checked')) {
        // Prevent refinement for being reset if the user clicks on an already checked radio button
        return;
      }
      if (parent.tagName === 'INPUT') {
        _this.refine(facetValueToRefine);
        return;
      }
      while (parent !== originalEvent.currentTarget) {
        if (parent.tagName === 'LABEL' && (parent.querySelector('input[type="checkbox"]') || parent.querySelector('input[type="radio"]'))) {
          return;
        }
        if (parent.tagName === 'A' && parent.href) {
          originalEvent.preventDefault();
        }
        parent = parent.parentNode;
      }
      originalEvent.stopPropagation();
      _this.refine(facetValueToRefine);
    });
    return _this;
  }
  _createClass(RefinementList, [{
    key: "shouldComponentUpdate",
    value: function shouldComponentUpdate(nextProps) {
      var areFacetValuesDifferent = !isEqual(this.props.facetValues, nextProps.facetValues);
      return areFacetValuesDifferent;
    }
  }, {
    key: "refine",
    value: function refine(facetValueToRefine) {
      this.lastRefinedValue = facetValueToRefine;
      this.props.toggleRefinement(facetValueToRefine);
    }
  }, {
    key: "componentWillReceiveProps",
    value: function componentWillReceiveProps(nextProps) {
      if (this.searchBox.current && !nextProps.isFromSearch) {
        this.searchBox.current.resetInput();
      }
    }

    /**
     * This sets focus on the last refined input element after a render
     * because Preact does not perform it automatically.
     * @see https://github.com/preactjs/preact/issues/3242
     */
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate() {
      var _this$listRef$current, _this$listRef$current2, _this$lastRefinedValu;
      (_this$listRef$current = this.listRef.current) === null || _this$listRef$current === void 0 ? void 0 : (_this$listRef$current2 = _this$listRef$current.querySelector("input[value=\"".concat((_this$lastRefinedValu = this.lastRefinedValue) === null || _this$lastRefinedValu === void 0 ? void 0 : _this$lastRefinedValu.replace('"', '\\"'), "\"]"))) === null || _this$listRef$current2 === void 0 ? void 0 : _this$listRef$current2.focus();
      this.lastRefinedValue = undefined;
    }
  }, {
    key: "refineFirstValue",
    value: function refineFirstValue() {
      var firstValue = this.props.facetValues && this.props.facetValues[0];
      if (firstValue) {
        var actualValue = firstValue.value;
        this.props.toggleRefinement(actualValue);
      }
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;
      var showMoreButtonClassName = cx(this.props.cssClasses.showMore, !(this.props.showMore === true && this.props.canToggleShowMore) && this.props.cssClasses.disabledShowMore);
      var showMoreButton = this.props.showMore === true && h(Template, _extends({}, this.props.templateProps, {
        templateKey: "showMoreText",
        rootTagName: "button",
        rootProps: {
          className: showMoreButtonClassName,
          disabled: !this.props.canToggleShowMore,
          onClick: this.props.toggleShowMore
        },
        data: {
          isShowingMore: this.props.isShowingMore
        }
      }));
      var shouldDisableSearchBox = this.props.searchIsAlwaysActive !== true && !(this.props.isFromSearch || !this.props.hasExhaustiveItems);
      var searchBox = this.props.searchFacetValues && h("div", {
        className: this.props.cssClasses.searchBox
      }, h(SearchBox, {
        ref: this.searchBox,
        placeholder: this.props.searchPlaceholder,
        disabled: shouldDisableSearchBox,
        cssClasses: this.props.cssClasses.searchable,
        templates: this.props.searchBoxTemplateProps.templates,
        onChange: function onChange(event) {
          return _this2.props.searchFacetValues(event.target.value);
        },
        onReset: function onReset() {
          return _this2.props.searchFacetValues('');
        },
        onSubmit: function onSubmit() {
          return _this2.refineFirstValue();
        }
        // This sets the search box to a controlled state because
        // we don't rely on the `refine` prop but on `onChange`.
        ,
        searchAsYouType: false,
        ariaLabel: "Search for filters"
      }));
      var facetValues = this.props.facetValues && this.props.facetValues.length > 0 && h("ul", {
        ref: this.listRef,
        className: this.props.cssClasses.list
      }, this.props.facetValues.map(this._generateFacetItem, this));
      var noResults = this.props.searchFacetValues && this.props.isFromSearch && (!this.props.facetValues || this.props.facetValues.length === 0) && h(Template, _extends({}, this.props.templateProps, {
        templateKey: "searchableNoResults",
        rootProps: {
          className: this.props.cssClasses.noResults
        }
      }));
      var rootClassName = cx(this.props.cssClasses.root, (!this.props.facetValues || this.props.facetValues.length === 0) && this.props.cssClasses.noRefinementRoot, this.props.className);
      return h("div", {
        className: rootClassName
      }, this.props.children, searchBox, facetValues, noResults, showMoreButton);
    }
  }]);
  return RefinementList;
}(Component);
_defineProperty(RefinementList, "defaultProps", defaultProps);
export default RefinementList;