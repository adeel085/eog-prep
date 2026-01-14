function ajaxCall(options) {
    if (options.url === undefined) {
        throw new Error("ajaxCall option required: url");
    }

    if (options.data === undefined) {
        throw new Error("ajaxCall option required: data");
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: options.url,
            method: options.method || "POST",
            data: options.data,
            dataType: options.dataType || "json",
            contentType: options.contentType || false,
            processData: options.processData || false,
            cache: options.cache || false,
            beforeSend: function (xhr) {
                if (
                    options.csrfHeader !== undefined &&
                    options.csrfHash !== undefined
                ) {
                    xhr.setRequestHeader(options.csrfHeader, options.csrfHash);
                }
            },
            success: (res) => {
                resolve(res);
            },
            error: (err) => {
                reject(err);
            },
        });
    });
}