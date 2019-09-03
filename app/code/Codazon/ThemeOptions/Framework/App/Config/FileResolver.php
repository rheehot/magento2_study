<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Framework\App\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        \Codazon\ThemeOptions\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->filesystem = $filesystem;
        $this->_moduleReader = $moduleReader;
    }

    public function get($filename, $scope)//not in use
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);
                $absolutePaths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolutePaths[] = $directory->getAbsolutePath($path);
                }
                $iterator = $this->iteratorFactory->create($absolutePaths);
                break;
            case 'global':
                $iterator = $this->_moduleReader->getConfigurationFiles($filename);
                break;
            default:
                $iterator = $this->_moduleReader->getConfigurationFiles($scope . '/' . $filename);
                break;
        }
        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function cdzget($filename, $scope, $themeId)
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);
                $absolutePaths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolutePaths[] = $directory->getAbsolutePath($path);
                }
                $iterator = $this->iteratorFactory->create($absolutePaths);
                break;
            case 'global':
                $iterator = $this->_moduleReader->cdzgetConfigurationFiles($filename, $themeId);
                break;
            default:
                $iterator = $this->_moduleReader->cdzgetConfigurationFiles($scope . '/' . $filename, $themeId);
                break;
        }
        return $iterator;
    }
}
