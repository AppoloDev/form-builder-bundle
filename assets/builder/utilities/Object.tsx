const isObject = (item: object) => {
    return (item && typeof item === 'object' && !Array.isArray(item));
}

export const merge = (target: object, ...sources: object[]): object => {
    if (sources && !sources.length) return target;
    const source = sources?.shift();

    if (source && isObject(target) && isObject(source)) {
        for (const key in source) {
            if (isObject(source[key as keyof typeof source])) {
                if (!target[key as keyof typeof source]) Object.assign(target, {
                    [key]: {}
                });

                merge(target[key as keyof typeof target], source[key as keyof typeof source]);
            } else {
                Object.assign(target, {
                    [key]: source[key as keyof typeof source]
                });
            }
        }
    }

    return merge(target, ...sources);
}
