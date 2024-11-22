function getWatchlistObjKey() {
    return 'watchlistv2';
}

// gets saved watchlist
function getWatchlistObj() {
    if (localStorage.watchlistv2 === undefined) {
        var watchlist = importWatchlistObjV1();
    } else {
        var watchlist = JSON.parse(localStorage.watchlistv2);
    }
    return watchlist;
}

function setWatchlistObj(watchlistObj) {
    localStorage.watchlistv2 = JSON.stringify(watchlistObj);
}

function getPortfolioObjKey() {
    return 'portfoliov1';
}

// gets saved portfolios
function getPortfolioObj() {
    if (localStorage.portfoliov1 === undefined) {
        var portfolio = [newPortfolioObject("My Portfolio")]
        setPortfolioObj(portfolio);
    } else {
        var portfolio = JSON.parse(LZUTF8.decompress(localStorage.portfoliov1, {"inputEncoding":"StorageBinaryString"}));
    }
    return portfolio;
}

function setPortfolioObj(portfolioObj) {
    localStorage.portfoliov1 = LZUTF8.compress(JSON.stringify(portfolioObj), {"outputEncoding":"StorageBinaryString"});
}

// import function for legacy watchlists
function importWatchlistObjV1() {
    if (localStorage.watchlistv1 === undefined) {
        var watchlistv1 = [];
    } else {
        var watchlistv1 = JSON.parse(localStorage.watchlistv1);
    }

    var watchlistv2 = [newWatchlistObject("My Watchlist")];

    watchlistv1.forEach(entry => {
        watchlistv2[0].watches.push(newWatchlistItem(
            Number(entry.item_id),
            entry.comment,
            entry.mark == null ? null : "gold",
            entry.mark,
        ));
    });

    localStorage.watchlistv2 = JSON.stringify(watchlistv2);
    return watchlistv2;
}

/**
 * Helper function to create a watchlist object in the correct format. Returns an object in the following format:
 *  {
 *      uid: <str>
 *      date_modified: <Unix millis>
 *      item_id: <int>
 *      mark_type: enum "gold", "sb", or null
 *      mark: <float> or null
 *      comment: <str>
 *      alert_date: <Unix millis> or null
 *      alert_price: <float> or null
 *  }
 * @param {int} item_id 
 * @param {string} comment
 * @param {string|null} mark_type Must be "gold", "sb", or null
 * @param {float|null} mark Must be float if mark_type is "gold" or "sb", or null otherwise
 * @param {int|null} alert_date Must be a millisecond Unix timestamp or null
 * @param {float|null} alert_price Must be float or null
 * @return {Object}
 */
function newWatchlistItem(item_id, comment = '', mark_type = null, mark = null, alert_date = null, alert_price = null) {
    if (mark_type !== null && mark_type !== 'gold' && mark_type !== 'sb') {
        mark_type = null;
        mark = null;
        console.error('Invalid watchlist benchmark type. Defaulting to none.');
    }

    return {
        'uid': "watch-" + uid(),
        'date_modified': Date.now(),
        'item_id': Number(item_id),
        'mark_type': (mark == null) ? null : mark_type,
        'mark': (mark_type == null || mark == null) ? null : Number(mark),
        'comment': comment,
        'alert_date': (alert_date == null) ? null : Number(alert_date),
        'alert_price': (alert_price == null) ? null : Number(alert_price),
    };
}

function newWatchlistObject(name) {
    return {
        'name': name,
        'uid': 'watchlist-' + uid(),
        'watches': [],
    }
}

/**
 * Helper function to create a portfolio position object in the correct format. Returns an object in the following format:
 *  {
 *      uid: <str>
 *      date_modified: <Unix millis>
 *      item_id: <int>
 *      qty: <int>
 *      mark: <float>
 *      mark_type: enum "gold", "sb"
 *      maturity_date: <Unix millis> or null
 *      maturity_price: <float> or null
 *  }
 * @param {int} item_id
 * @param {int} qty
 * @param {float} mark
 * @param {string} mark_type Must be "gold" (default) or "sb"
 * @param {int|null} maturity_date Must be a millisecond Unix timestamp or null
 * @param {float|null} maturity_price Must be float or null
 * @return {Object}
 */
 function newPortfolioPosition(item_id, qty, mark, mark_type = "gold", maturity_date = null, maturity_price = null) {
    if (mark_type !== 'gold' && mark_type !== 'sb') {
        throw 'Invalid portfolio benchmark type.';
    }

    return {
        'uid': "position-" + uid(),
        'date_modified': Date.now(),
        'item_id': Number(item_id),
        'qty': Number(qty),
        'mark': Number(mark),
        'mark_type': mark_type,
        'maturity_date': (maturity_date == null) ? null : Number(maturity_date),
        'maturity_price': (maturity_price == null) ? null : Number(maturity_price),
    };
}

function newPortfolioObject(name) {
    return {
        'name': name,
        'uid': 'portfolio-' + uid(),
        'date_created': Date.now(),
        'positions': [],
    }
}

function exportPortfolio() {
    return JSON.stringify(getPortfolioObj());
}

function importPortfolio(string) {
    let object = JSON.parse(string);

    if (!object[0].uid.includes('portfolio')) {
        console.error('Invalid input. Import aborted');
        return;
    }

    setPortfolioObj(object);
    console.log(`imported ${getPortfolioObj().length} portfolios`);
}

function exportWatchlist() {
    return JSON.stringify(getWatchlistObj());
}

function importWatchlist(string) {
    let object = JSON.parse(string);
    if (!object[0].uid.includes('watchlist')) {
        console.error('Invalid input. Import aborted');
        return;
    }

    setWatchlistObj(object);
    console.log(`imported ${getWatchlistObj().length} watchlists`)
}

function getUserDataString() {
    let data = {};
    data[getPortfolioObjKey()] = getPortfolioObj();
    data[getWatchlistObjKey()] = getWatchlistObj();

    return LZUTF8.compress(JSON.stringify(data), {"outputEncoding": "Base64"});
}

function decodeUserDataString(str) {
    return JSON.parse(LZUTF8.decompress(str, {"inputEncoding": "Base64"}));
}