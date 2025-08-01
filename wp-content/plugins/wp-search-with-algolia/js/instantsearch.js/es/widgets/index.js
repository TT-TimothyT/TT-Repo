import { deprecate } from "../lib/utils/index.js";
import answers from "./answers/answers.js";
import configureRelatedItems from "./configure-related-items/configure-related-items.js";
import dynamicWidgets from "./dynamic-widgets/dynamic-widgets.js";

/** @deprecated answers is no longer supported */
export var EXPERIMENTAL_answers = deprecate(answers, 'answers is no longer supported');

/** @deprecated use relatedItems instead */
export var EXPERIMENTAL_configureRelatedItems = deprecate(configureRelatedItems, 'EXPERIMENTAL_configureRelatedItems is deprecated and will be removed in a next minor version of InstantSearch. Please use relatedItems instead.');

/** @deprecated use dynamicWidgets */
export var EXPERIMENTAL_dynamicWidgets = deprecate(dynamicWidgets, 'use dynamicWidgets');
export { dynamicWidgets };
export { default as analytics } from "./analytics/analytics.js";
export { default as breadcrumb } from "./breadcrumb/breadcrumb.js";
export { default as clearRefinements } from "./clear-refinements/clear-refinements.js";
export { default as configure } from "./configure/configure.js";
export { default as currentRefinements } from "./current-refinements/current-refinements.js";
export { default as geoSearch } from "./geo-search/geo-search.js";
export { default as hierarchicalMenu } from "./hierarchical-menu/hierarchical-menu.js";
export { default as hits } from "./hits/hits.js";
export { default as hitsPerPage } from "./hits-per-page/hits-per-page.js";
export { default as index } from "./index/index.js";
export { default as infiniteHits } from "./infinite-hits/infinite-hits.js";
export { default as menu } from "./menu/menu.js";
export { default as menuSelect } from "./menu-select/menu-select.js";
export { default as numericMenu } from "./numeric-menu/numeric-menu.js";
export { default as pagination } from "./pagination/pagination.js";
export { default as panel } from "./panel/panel.js";
export { default as places } from "./places/places.js";
export { default as poweredBy } from "./powered-by/powered-by.js";
export { default as queryRuleContext } from "./query-rule-context/query-rule-context.js";
export { default as queryRuleCustomData } from "./query-rule-custom-data/query-rule-custom-data.js";
export { default as relatedProducts } from "./related-products/related-products.js";
export { default as rangeInput } from "./range-input/range-input.js";
export { default as rangeSlider } from "./range-slider/range-slider.js";
export { default as ratingMenu } from "./rating-menu/rating-menu.js";
export { default as refinementList } from "./refinement-list/refinement-list.js";
export { default as relevantSort } from "./relevant-sort/relevant-sort.js";
export { default as searchBox } from "./search-box/search-box.js";
export { default as sortBy } from "./sort-by/sort-by.js";
export { default as stats } from "./stats/stats.js";
export { default as toggleRefinement } from "./toggle-refinement/toggle-refinement.js";
export { default as trendingItems } from "./trending-items/trending-items.js";
export { default as voiceSearch } from "./voice-search/voice-search.js";
export { default as frequentlyBoughtTogether } from "./frequently-bought-together/frequently-bought-together.js";
export { default as lookingSimilar } from "./looking-similar/looking-similar.js";