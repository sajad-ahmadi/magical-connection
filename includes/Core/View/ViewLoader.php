<?php
declare(strict_types=1);

namespace MagicalConnection\Core\View;

use MagicalConnection\Support\MagicalConnectionException;

/**
 * Load and render PHP view templates.
 *
 * Resolves dot-notation view names to PHP template files and provides
 * both direct rendering and buffered rendering for application views.
 *
 * @since   1.0.0
 * @package CodeArt
 */
class ViewLoader
{
    /**
     * Absolute path to the application's views directory.
     *
     * @since 1.0.0
     */
    private string $viewsPath;

    /**
     * Initialize the view loader.
     *
     * @since 1.0.0
     *
     * @param string $viewsPath Absolute path to the views directory.
     */
    public function __construct(string $viewsPath)
    {
        $this->viewsPath = $viewsPath;
    }

    /**
     * Render a view directly to the output.
     *
     * Dot notation in the view name is converted to directory separators.
     *
     * @param string $view View name.
     * @param array $data Data made available to the view.
     * @param string $extension Template file extension without the leading dot.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function render(string $view, array $data = [], string $extension = 'php' ): void {
        $path = $this->getPath($view, $extension);
        $this->load($path, $data);
    }

    /**
     * Render a view and return its output as a string.
     *
     * Dot notation in the view name is converted to directory separators.
     *
     * @param string $view View name.
     * @param array<string,mixed> $data Data made available to the view.
     * @param string $extension Template file extension without the leading dot.
     *
     * @return string Rendered view output.
     *
     * @since 1.0.0
     */
    public function make(string $view, array $data = [], string $extension = 'php'): string {
        $path = $this->getPath($view, $extension);
        ob_start();
        $this->load($path, $data);
        return ob_get_clean() ?: '';
    }

    /**
     * Resolve a view name to its template path.
     *
     * Dot notation is converted to directory separators before resolving
     * the template relative to the configured views directory.
     *
     * @param string $view View name using dot notation.
     * @param string $extension Template file extension without the leading dot.
     *
     * @return string Absolute path to the view template.
     *
     * @since 1.0.0
     */
    private function getPath(string $view, string $extension): string
    {
        $view = str_replace('.', DIRECTORY_SEPARATOR, $view);

        $path = $this->viewsPath . DIRECTORY_SEPARATOR . $view . '.' . $extension ;

        if (!is_file($path)) {
            throw new MagicalConnectionException(
                "View [{$view}] not found.",
                'view_not_found'
            );
        }

        return $path;
    }

    /**
     * Load a view template with the provided data.
     *
     * @since 1.0.0
     *
     * @param string $path               Absolute path to the view template.
     * @param array $data Data made available to the view.
     *
     * @return void
     */
    private function load(string $path, array $data): void
    {
        extract($data, EXTR_SKIP);
        require $path;
    }

}